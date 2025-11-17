<?php
// Notifier (Queue-aware, Backward-compatible, Safe)
// Handles Email (Free) + Telegram & Slack (Pro)
// Integrates with NH_Queue for async delivery.

if (!defined('ABSPATH')) exit;

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('✅ NH_Notifier v1.6.0 loaded'); // updated version tag
}

class NH_Notifier {

    protected $r; // Registry reference

    public function __construct($registry = null) {
        $this->r = $registry;
    }

    /**
     * Queue a notification (preferred entry point for async send)
     */
    public function queue_send(string $channel, array $payload = []) : bool {

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("🚀 NH_Notifier::queue_send {$channel} title=" . ($payload['title'] ?? ''));
        }

        $db = $this->r ? $this->r->get_svc('db') : null;
        $notif_id = 0;

        // --------------------------------------------------------
        // IF no_log = true → send but DO NOT log into DB
        // --------------------------------------------------------
        if (!empty($payload['no_log'])) {

            $payload['notification_id'] = 0;

        } else if ($db && method_exists($db, 'insert_notification')) {

            // --------------------------------------------------------
            // BASE FIELDS
            // --------------------------------------------------------
            $source  = strtolower(trim($payload['source'] ?? ''));
            $type    = strtolower(trim($payload['type']   ?? ''));
            $title   = $payload['title']  ?? '';
            $message = $payload['body']   ?? ($payload['message'] ?? '');

            $contextJson = isset($payload['context']) ? wp_json_encode($payload['context']) : null;
            error_log("🔥 RAW PAYLOAD: " . print_r($payload, true));

            // --------------------------------------------------------
            // PRIORITY NORMALIZATION
            // --------------------------------------------------------
            $priority = isset($payload['priority']) ? (int)$payload['priority'] : null;

            if (!$type) {
                if (!empty($payload['event_type'])) {
                    $type = strtolower($payload['event_type']);
                } elseif (!empty($payload['context']['type'])) {
                    $type = strtolower($payload['context']['type']);
                }
            }

            if (!$source && !empty($payload['context']['source'])) {
                $source = strtolower($payload['context']['source']);
            }

            if ($priority === null) {

                if (str_contains($source, 'woocommerce') || str_contains($type, 'order')) {
                    $priority = 80;
                } elseif (str_contains($type, 'comment')) {
                    $priority = 60;
                } elseif (
                    str_contains($source, 'cf7') ||
                    str_contains($type,   'form') ||
                    str_contains($type,   'cf7')
                ) {
                    $priority = 55;
                } elseif (
                    str_contains($source, 'security') ||
                    str_contains($source, 'wordfence') ||
                    str_contains($type,   'security') ||
                    str_contains($type,   'error')
                ) {
                    $priority = 90;
                } else {
                    $priority = 50;
                }
            }

            $priority = max(0, min(100, (int)$priority));

            // --------------------------------------------------------
            // TAGS NORMALIZATION
            // --------------------------------------------------------
            if (!empty($payload['tags'])) {

                $tagsArr = is_string($payload['tags'])
                    ? json_decode($payload['tags'], true)
                    : (array)$payload['tags'];

                if (!is_array($tagsArr)) {
                    $tagsArr = [(string)$payload['tags']];
                }

                $tagsArr = array_values(array_unique(array_map('strval', $tagsArr)));
                $tagsJson = wp_json_encode($tagsArr);

            } else {
                $fallback = array_values(array_unique(array_filter([
                    (string)$source,
                    (string)$type,
                ])));
                $tagsJson = $fallback ? wp_json_encode($fallback) : null;
            }

            // --------------------------------------------------------
            // INSERT INTO DATABASE
            // --------------------------------------------------------
            $notif_id = $db->insert_notification([
                'source'     => $source,
                'type'       => $type,
                'title'      => $title,
                'message'    => $message,
                'status'     => 0,
                'context'    => $contextJson,
                'priority'   => $priority,
                'tags'       => $tagsJson,
            ]);
        }

        // attach ID for send_now()
        $payload['notification_id'] = $notif_id;

        // --------------------------------------------------------
        // QUEUE or DIRECT SEND
        // --------------------------------------------------------
        if (class_exists('NH_Queue')) {
            NH_Queue::enqueue_send($channel, $payload);
        } else {
            $this->send_now($channel, $payload);
        }

        return true;
    }


    /**
     * Legacy alias for send_now()
     */
    public function send($channel, $payload = []) {
        return $this->send_now($channel, $payload);
    }

    /**
     * Perform the actual delivery (synchronous)
     */
    public function send_now($channel, $payload = []) {
        $channel = sanitize_key($channel);
        $ok  = false;
        $err = '';

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("📨 NH_Notifier::send_now {$channel}");
            error_log('🔍 NH_License::is_pro = ' . (class_exists('NH_License') && NH_License::is_pro() ? 'true' : 'false'));
        }
        // --------------------------------------------------------
        // Apply network policy (Pro)
        // --------------------------------------------------------
        if (function_exists('get_site_option') && is_multisite()) {
            $policy = get_site_option('nh_network_policy', []);

            // Restrict allowed channels
            if (!empty($policy['channels']) && is_array($policy['channels'])) {
                if (!in_array($channel, $policy['channels'], true)) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("🚫 NH_Notifier blocked channel={$channel} by network policy");
                    }
                    return false; // block this channel entirely
                }
            }

            // Override email recipient
            if ($channel === 'email' && !empty($policy['email_to'])) {
                $to_override = sanitize_email($policy['email_to']);
                if (!empty($to_override)) {
                    $payload['override_email_to'] = $to_override;
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("📧 NH_Notifier override email_to={$to_override}");
                    }
                }
            }
        }

        switch ($channel) {
            case 'email':
                $ok = $this->send_email($payload);
                break;

            case 'telegram':
                if (class_exists('NH_License') && NH_License::is_pro()) {
                    $ok = $this->send_telegram($payload);
                } else {
                    $err = 'Telegram skipped (Pro only or invalid license)';
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('NH_Notifier: '.$err);
                    }
                }
                break;

            case 'slack':
                if (class_exists('NH_License') && NH_License::is_pro()) {
                    $ok = $this->send_slack($payload);
                } else {
                    $err = 'Slack skipped (Pro only or invalid license)';
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('NH_Notifier: '.$err);
                    }                
                }
                break;

            default:
                $err = 'Unknown channel ' . $channel;
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('NH_Notifier: '.$err);
                }                
                break;
        }

        // Update DB status if possible
        $notif_id = isset($payload['notification_id']) ? intval($payload['notification_id']) : 0;
        $db = $this->r ? $this->r->get_svc('db') : null;

        if ($notif_id && $db && method_exists($db, 'log_delivery_status')) {
            $db->log_delivery_status($notif_id, [
                'status'     => $ok ? 'sent' : 'error',
                'error_msg'  => $ok ? '' : $err,
                'channel'    => $channel,
                'updated_at' => current_time('mysql'),
            ]);
        }

        return $ok;
    }

    // ---------------------------------------------------------------------
    //  Email (Free)
    // ---------------------------------------------------------------------
    protected function send_email($payload) {
        $to = $payload['override_email_to']
            ?? get_option('nh_email_to', get_option('admin_email'));

        $subject = $payload['title'] ?? ($payload['subject'] ?? __('Notification Hub', 'notification-hub'));
        $message = $payload['body'] ?? ($payload['message'] ?? print_r($payload, true));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("📧 NH_Notifier::send_email to={$to} subject={$subject}");
        }

        try {
            return wp_mail($to, $subject, $message);
        } catch (Throwable $e) {
            error_log('❌ NH_Notifier::send_email failed: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------------
    //  Telegram (Pro)
    // ---------------------------------------------------------------------
    protected function send_telegram($payload) {
        if (self::is_localhost()) {
            error_log("🚀 Localhost detected — sending Telegram immediately (no queue delay)");
        }

        $token = get_option('nh_telegram_bot_token', '');
        if (empty($token)) {
            $token = get_option('nh_telegram_token', '');
        }

        $chat = get_option('nh_telegram_chat_id', '');
        if (empty($token) || empty($chat)) {
            error_log('⚠️ NH_Notifier: Telegram missing token or chat_id');
            return false;
        }

        $text = $payload['body'] ?? ($payload['message'] ?? '(no message)');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("📨 NH_Notifier::send_telegram chat={$chat} text={$text}");
        }

        try {
            $resp = wp_remote_post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'body'    => [
                        'chat_id' => $chat,
                        'text'    => $text,
                    ],
                    'timeout' => 5,
                ]
            );
            if (is_wp_error($resp)) {
                error_log('❌ NH_Notifier::send_telegram WP_Error: ' . $resp->get_error_message());
                return false;
            }
            return wp_remote_retrieve_response_code($resp) === 200;
        } catch (Throwable $e) {
            error_log('❌ NH_Notifier::send_telegram failed: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------------
    //  Slack (Pro)
    // ---------------------------------------------------------------------
    protected function send_slack($payload) {
        if (self::is_localhost()) {
            error_log("🚀 Localhost detected — sending Slack immediately (no queue delay)");
        }

        $hook = get_option('nh_slack_webhook', '');
        if (empty($hook)) {
            $hook = get_option('nh_slack_url', '');
        }

        if (empty($hook)) {
            error_log('⚠️ NH_Notifier: Slack webhook not configured');
            return false;
        }

        $text = $payload['body'] ?? ($payload['message'] ?? '(no message)');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("💬 NH_Notifier::send_slack posting to={$hook}");
        }

        try {
            $resp = wp_remote_post($hook, [
                'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
                'body'    => wp_json_encode(['text' => $text]),
                'timeout' => 5,
            ]);
            if (is_wp_error($resp)) {
                error_log('❌ NH_Notifier::send_slack WP_Error: ' . $resp->get_error_message());
                return false;
            }
            return wp_remote_retrieve_response_code($resp) === 200;
        } catch (Throwable $e) {
            error_log('❌ NH_Notifier::send_slack failed: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------------
    protected static function is_localhost(): bool {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $server = $_SERVER['SERVER_ADDR'] ?? '';
        if (in_array($host, ['localhost', '127.0.0.1'], true)) return true;
        if (str_ends_with($host, '.test') || str_contains($host, '.local')) return true;
        if (in_array($server, ['127.0.0.1', '::1'], true)) return true;
        return false;
    }
}
