<?php
/**
 * Queue Management & DB Logging
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Notifier_Queue {

    private $registry;

    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
     * Queue notification for async delivery
     */
    public function queue_send(string $channel, array $payload): bool {
        if (WP_DEBUG) {
            error_log("🚀 Queueing: {$channel} - " . ($payload['title'] ?? 'no title'));
        }

        // Skip DB logging if requested
        if (!empty($payload['no_log'])) {
            $payload['notification_id'] = 0;
        } else {
            $payload['notification_id'] = $this->log_to_database($payload);
        }

        // Queue or send immediately
        if (class_exists('NH_Queue')) {
            NH_Queue::enqueue_send($channel, $payload);
        } else {
            return $this->send_now($channel, $payload);
        }

        return true;
    }

    /**
     * Log notification to database
     */
    private function log_to_database(array $payload): int {
        $db = $this->registry->get_svc('db');
        
        if (!$db || !method_exists($db, 'insert_notification')) {
            return 0;
        }

        $data = $this->normalize_payload($payload);

        return $db->insert_notification([
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
     * Normalize payload data
     */
    private function normalize_payload(array $payload): array {
        $source  = strtolower(trim($payload['source'] ?? ''));
        $type    = strtolower(trim($payload['type'] ?? ''));
        $title   = $payload['title'] ?? '';
        $message = $payload['body'] ?? $payload['message'] ?? '';

        // Auto-detect type/source from context
        if (!$type) {
            $type = $payload['event_type'] ?? $payload['context']['type'] ?? '';
        }
        if (!$source) {
            $source = $payload['context']['source'] ?? '';
        }

        return [
            'source'   => $source,
            'type'     => strtolower($type),
            'title'    => $title,
            'message'  => $message,
            'context'  => isset($payload['context']) ? wp_json_encode($payload['context']) : null,
            'priority' => $this->calculate_priority($source, $type, $payload['priority'] ?? null),
            'tags'     => $this->normalize_tags($payload['tags'] ?? null, $source, $type),
        ];
    }

    /**
     * Calculate priority based on source/type
     */
    private function calculate_priority(string $source, string $type, $explicit_priority): int {
        if ($explicit_priority !== null) {
            return max(0, min(100, (int)$explicit_priority));
        }

        // WooCommerce orders
        if (str_contains($source, 'woocommerce') || str_contains($type, 'order')) {
            return 80;
        }

        // Security alerts
        if (str_contains($source, 'security') || str_contains($source, 'wordfence') || 
            str_contains($type, 'security') || str_contains($type, 'error')) {
            return 90;
        }

        // Comments
        if (str_contains($type, 'comment')) {
            return 60;
        }

        // Forms (CF7)
        if (str_contains($source, 'cf7') || str_contains($type, 'form') || str_contains($type, 'cf7')) {
            return 55;
        }

        return 50; // Default
    }

    /**
     * Normalize tags
     */
    private function normalize_tags($tags, string $source, string $type): ?string {
        if (!empty($tags)) {
            $tagsArr = is_string($tags) ? json_decode($tags, true) : (array)$tags;
            
            if (!is_array($tagsArr)) {
                $tagsArr = [(string)$tags];
            }

            $tagsArr = array_values(array_unique(array_map('strval', $tagsArr)));
            return wp_json_encode($tagsArr);
        }

        // Fallback: use source + type as tags
        $fallback = array_values(array_unique(array_filter([$source, $type])));
        return $fallback ? wp_json_encode($fallback) : null;
    }

    /**
     * Update delivery status in DB
     */
    public function log_delivery_status(int $notif_id, string $channel, bool $success, string $error = ''): void {
        if (!$notif_id) return;

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
     * Send notification immediately (bypass queue)
     */
    private function send_now(string $channel, array $payload): bool {
        $notifier = new NH_Notifier($this->registry);
        return $notifier->send_now($channel, $payload);
    }
}
