<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Helpers\Security;

/**
 * Admin AJAX route: Load a notification for modal preview.
 *
 * @since 1.7.2
 */
final class LoadNotification {
    /**
     * @since 1.7.2
     */
    public function handle(): void {
        Security::ensureCanManageOptions();

        $id    = isset($_REQUEST['id']) ? absint(wp_unslash($_REQUEST['id'])) : 0;
        $nonce = isset($_REQUEST['_wpnonce'])
            ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']))
            : (isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '');

        if (!$id || !wp_verify_nonce($nonce, 'nh_view_' . $id)) {
            wp_send_json_error(['message' => esc_html__('Invalid request.', 'notification-hub')], 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));

        if (!$row) {
            wp_send_json_error(['message' => esc_html__('Notification not found.', 'notification-hub')], 404);
        }

        $payload = [
            'title'   => (string) $row->title,
            'summary' => (string) $row->message,
            'source'  => (string) $row->source,
            'type'    => isset($row->type) ? (string) $row->type : '',
            'context' => !empty($row->context) ? json_decode((string) $row->context, true) : [],
            'link'    => isset($row->link) ? (string) $row->link : '',
            'no_log'  => true,
        ];

        if (!is_array($payload['context'])) {
            $payload['context'] = [];
        }

        // Prefer real template render when available; fallback to summary.
        $email    = class_exists('NH_Template') ? NH_Template::render_notification('email', $payload) : $payload['summary'];
        $telegram = class_exists('NH_Template') ? NH_Template::render_notification('telegram', $payload) : $payload['summary'];
        $slack    = class_exists('NH_Template') ? NH_Template::render_notification('slack', $payload) : $payload['summary'];

        wp_send_json_success([
            'title'      => (string) $row->title,
            'message'    => (string) $row->message,
            'source'     => (string) $row->source,
            'status'     => (int) $row->status,
            'created_at' => mysql2date('Y-m-d H:i', (string) $row->created_at),
            'payload'    => $payload,
            'preview'    => [
                'email'    => $email,
                'telegram' => $telegram,
                'slack'    => $slack,
            ],
        ]);
    }
}
