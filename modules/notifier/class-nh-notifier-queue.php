<?php
/**
 * NH_Notifier_Queue
 *
 * Queue management and database logging for notifications.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier_Queue {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var mixed
     */
    private $registry;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry/container instance.
     */
    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
     * Queue notification for async delivery.
     *
     * Payload special keys:
     * - no_log (bool) Optional. Skip DB logging.
     * - notification_id (int) Filled when logged.
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload.
     * @return bool
     */
    public function queue_send(string $channel, array $payload): bool {
        $channel = sanitize_key($channel);
        $payload = is_array($payload) ? $payload : [];

        $title_for_log = '';
        if (!empty($payload['title']) && is_string($payload['title'])) {
            $title_for_log = $payload['title'];
        } elseif (!empty($payload['subject']) && is_string($payload['subject'])) {
            $title_for_log = $payload['subject'];
        }

        $this->debug_log(
            sprintf(
                /* translators: 1: channel, 2: title */
                __('Queueing notification (channel: %1$s, title: %2$s)', 'notification-hub'),
                $channel,
                $title_for_log !== '' ? $title_for_log : __('(no title)', 'notification-hub')
            )
        );

        // Skip DB logging if requested.
        if (!empty($payload['no_log'])) {
            $payload['notification_id'] = 0;
        } else {
            $payload['notification_id'] = $this->log_to_database($payload);
        }

        // Queue or send immediately (fallback).
        if (class_exists('NH_Queue') && method_exists('NH_Queue', 'enqueue_send')) {
            NH_Queue::enqueue_send($channel, $payload);
            return true;
        }

        // Fallback: send immediately without re-entering NH_Notifier (avoid loops).
        return $this->send_immediate_fallback($channel, $payload);
    }

    /**
     * Log notification to database.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return int Inserted notification ID, or 0 on failure.
     */
    private function log_to_database(array $payload): int {
        if (!is_object($this->registry) || !method_exists($this->registry, 'get_svc')) {
            return 0;
        }

        $db = $this->registry->get_svc('db');

        if (!$db || !method_exists($db, 'insert_notification')) {
            return 0;
        }

        $data = $this->normalize_payload($payload);

        return (int) $db->insert_notification([
            'source'   => $data['source'],
            'type'     => $data['type'],
            'title'    => $data['title'],
            'message'  => $data['message'],
            'status'   => 0,
            'context'  => $data['context'],
            'priority' => $data['priority'],
            'tags'     => $data['tags'],
        ]);
    }

    /**
     * Normalize payload data for DB.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return array<string, mixed>
     */
    private function normalize_payload(array $payload): array {
        $context = [];
        if (!empty($payload['context']) && is_array($payload['context'])) {
            $context = $payload['context'];
        }

        $source = isset($payload['source']) ? strtolower(trim((string) $payload['source'])) : '';
        $type   = isset($payload['type']) ? strtolower(trim((string) $payload['type'])) : '';

        $title = '';
        if (!empty($payload['title']) && is_string($payload['title'])) {
            $title = $payload['title'];
        }

        $message = '';
        if (!empty($payload['body']) && is_string($payload['body'])) {
            $message = $payload['body'];
        } elseif (!empty($payload['message']) && is_string($payload['message'])) {
            $message = $payload['message'];
        }

        // Auto-detect type/source from context.
        if ($type === '') {
            if (!empty($payload['event_type'])) {
                $type = strtolower(trim((string) $payload['event_type']));
            } elseif (!empty($context['type'])) {
                $type = strtolower(trim((string) $context['type']));
            }
        }

        if ($source === '' && !empty($context['source'])) {
            $source = strtolower(trim((string) $context['source']));
        }

        $priority = $this->calculate_priority($source, $type, $payload['priority'] ?? null);
        $tags     = $this->normalize_tags($payload['tags'] ?? null, $source, $type);

        return [
            'source'   => $source,
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'context'  => !empty($context) ? wp_json_encode($context) : null,
            'priority' => $priority,
            'tags'     => $tags,
        ];
    }

    /**
     * Calculate priority based on source/type.
     *
     * @since 1.6.2
     * @param string $source Source slug.
     * @param string $type Type slug.
     * @param mixed  $explicit_priority Explicit priority.
     * @return int
     */
    private function calculate_priority(string $source, string $type, $explicit_priority): int {
        if ($explicit_priority !== null && $explicit_priority !== '') {
            return max(0, min(100, (int) $explicit_priority));
        }

        // WooCommerce orders.
        if ($source !== '' && function_exists('str_contains') && (str_contains($source, 'woocommerce') || str_contains($type, 'order'))) {
            return 80;
        }

        // Security alerts.
        if (function_exists('str_contains')) {
            $is_security = (
                ($source !== '' && (str_contains($source, 'security') || str_contains($source, 'wordfence'))) ||
                ($type !== '' && (str_contains($type, 'security') || str_contains($type, 'error')))
            );
            if ($is_security) {
                return 90;
            }
        }

        // Comments.
        if ($type !== '' && function_exists('str_contains') && str_contains($type, 'comment')) {
            return 60;
        }

        // Forms (CF7).
        if (function_exists('str_contains')) {
            $is_forms = (
                ($source !== '' && str_contains($source, 'cf7')) ||
                ($type !== '' && (str_contains($type, 'form') || str_contains($type, 'cf7')))
            );
            if ($is_forms) {
                return 55;
            }
        }

        return 50;
    }

    /**
     * Normalize tags for DB.
     *
     * @since 1.6.2
     * @param mixed  $tags Tags input.
     * @param string $source Source slug.
     * @param string $type Type slug.
     * @return string|null JSON encoded tags or null.
     */
    private function normalize_tags($tags, string $source, string $type): ?string {
        $tags_arr = [];

        if (!empty($tags)) {
            if (is_string($tags)) {
                $decoded = json_decode($tags, true);
                $tags_arr = is_array($decoded) ? $decoded : [$tags];
            } elseif (is_array($tags)) {
                $tags_arr = $tags;
            } else {
                $tags_arr = [(string) $tags];
            }
        } else {
            // Fallback: use source + type as tags.
            $tags_arr = array_filter([$source, $type]);
        }

        $tags_arr = array_values(array_unique(array_map('strval', $tags_arr)));

        return !empty($tags_arr) ? wp_json_encode($tags_arr) : null;
    }

    /**
     * Update delivery status in DB.
     *
     * @since 1.6.2
     * @param int    $notif_id Notification ID.
     * @param string $channel Channel slug.
     * @param bool   $success Success status.
     * @param string $error Error message.
     * @return void
     */
    public function log_delivery_status(int $notif_id, string $channel, bool $success, string $error = ''): void {
        if (!$notif_id) {
            return;
        }

        if (!is_object($this->registry) || !method_exists($this->registry, 'get_svc')) {
            return;
        }

        $db = $this->registry->get_svc('db');

        if (!$db || !method_exists($db, 'log_delivery_status')) {
            return;
        }

        $db->log_delivery_status($notif_id, [
            'status'     => $success ? 'sent' : 'error',
            'error_msg'  => $error,
            'channel'    => $channel,
            'updated_at' => current_time('mysql'),
        ]);
    }

    /**
     * Send immediately when NH_Queue is not available.
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload.
     * @return bool
     */
    private function send_immediate_fallback(string $channel, array $payload): bool {
        $channel = sanitize_key($channel);

        switch ($channel) {
            case 'email':
                if (!class_exists('NH_Notifier_Email')) {
                    $this->debug_log('Notification Hub: Email handler missing.');
                    return false;
                }
                return (bool) NH_Notifier_Email::send($payload);

            case 'telegram':
                if (!class_exists('NH_Notifier_Telegram')) {
                    $this->debug_log('Notification Hub: Telegram handler missing.');
                    return false;
                }
                return (bool) NH_Notifier_Telegram::send($payload);

            case 'slack':
                if (!class_exists('NH_Notifier_Slack')) {
                    $this->debug_log('Notification Hub: Slack handler missing.');
                    return false;
                }
                return (bool) NH_Notifier_Slack::send($payload);

            default:
                $this->debug_log(sprintf('Notification Hub: Unknown channel %s', $channel));
                return false;
        }
    }

    /**
     * Debug logger.
     *
     * @since 1.6.2
     * @param string $message Log message.
     * @return void
     */
    private function debug_log(string $message): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log($message);
        }
    }
}
