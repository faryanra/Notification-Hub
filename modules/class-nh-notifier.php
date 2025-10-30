<?php
// Notifier (Queue-aware, Backward-compatible, Safe)
// Handles Email (Free) + Telegram & Slack (Pro)
// Integrates with NH_Queue for async delivery.

if (!defined('ABSPATH')) exit;

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('✅ NH_Notifier v1.4.1 loaded');
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

        // Save to DB as pending
        if ($db && method_exists($db, 'insert_notification')) {
            $payload['type']    = $payload['type'] ?? $channel;
            $payload['message'] = $payload['body'] ?? ($payload['message'] ?? '');
            $notif_id = $db->insert_notification([
                'source'     => $payload['source'] ?? '',
                'type'       => $payload['type'],
                'title'      => $payload['title'] ?? '',
                'message'    => $payload['message'],
                'status'     => 'pending',
                'error_msg'  => '',
                'created_at' => current_time('mysql'),
            ]);
        }

        // Attach ID for later update
        $payload['notification_id'] = $notif_id;

        // Enqueue or fallback to direct send
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

        switch ($channel) {
            case 'email':
                $ok = $this->send_email($payload);
                break;

            case 'telegram':
                if (class_exists('NH_License') && NH_License::is_pro()) {
                    $ok = $this->send_telegram($payload);
                } else {
                    $err = 'Telegram skipped (Pro only or invalid license)';
                    error_log('NH_Notifier: '.$err);
                }
                break;

            case 'slack':
                if (class_exists('NH_License') && NH_License::is_pro()) {
                    $ok = $this->send_slack($payload);
                } else {
                    $err = 'Slack skipped (Pro only or invalid license)';
                    error_log('NH_Notifier: '.$err);
                }
                break;

            default:
                $err = 'Unknown channel ' . $channel;
                error_log('NH_Notifier: '.$err);
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
        $to = get_option('nh_email_to', get_option('admin_email'));

        $subject = $payload['title'] ?? ($payload['subject'] ?? __('Notification Hub', 'notification-hub'));
        $message = $payload['body']  ?? ($payload['message'] ?? wp_json_encode($payload));

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
        // backward-compatible option names
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
        // backward-compatible fallback
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
}
