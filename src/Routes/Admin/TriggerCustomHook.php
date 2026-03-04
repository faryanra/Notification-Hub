<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: trigger/test custom hook.
 *
 * Expects: ?action=nh_test_hook&id=123&_wpnonce=...
 * Nonce: nh_test_hook_{id}
 *
 * @since 1.7.2
 */
final class TriggerCustomHook {
    public function handle(): void {
        Capabilities::ensureManageOptions();

        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if ($id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_tested=0'));
            exit;
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'nh_test_hook_' . $id)) {
            wp_die(esc_html__('Invalid nonce.', 'notification-hub'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        $hook = null;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $hook = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));

        if (!$hook) {
            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_tested=0'));
            exit;
        }

        // Trigger the configured WP action name as-is.
        $actionName = isset($hook->action_name) ? (string) $hook->action_name : '';
        if ($actionName !== '') {
            do_action($actionName, $hook);
        }

        // Best-effort: send a test notification via legacy notifier if it exists.
        $ok = true;
        try {
            if (class_exists('NH_Notifier')) {
                $notifier = new \NH_Notifier();
                if (method_exists($notifier, 'send')) {
                    $channels = [];
                    if (isset($hook->channels)) {
                        $decoded = json_decode((string) $hook->channels, true);
                        $channels = is_array($decoded) ? $decoded : [];
                    }

                    if (empty($channels)) {
                        $channels = ['email'];
                    }

                    foreach ($channels as $ch) {
                        $notifier->send((string) $ch, [
                            'title'  => esc_html__('Notification Hub Hook Test', 'notification-hub'),
                            'body'   => esc_html__('Test triggered from Hooks page.', 'notification-hub'),
                            'source' => 'hook_test',
                            'type'   => 'test',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            $ok = false;
        }

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_tested=' . ($ok ? '1' : '0')));
        exit;
    }
}
