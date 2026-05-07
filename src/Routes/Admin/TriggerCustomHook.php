<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Security\Capabilities;
use NotificationHub\Services\ServiceFactory;

/**
 * Admin-post: trigger/test custom hook.
 *
 * Expects: ?action=nh_test_hook&id=123&_wpnonce=...
 * Nonce: nh_test_hook_{id}
 *
 * @since 1.0.0
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

        // Send a direct test notification to selected channels.
        $ok = true;
        try {
            $channels = [];
            if (isset($hook->channels)) {
                $decoded = json_decode((string) $hook->channels, true);
                $channels = is_array($decoded) ? $decoded : [];
            }

            if (empty($channels)) {
                $channels = ['email'];
            }

            $dispatcher = ServiceFactory::makeNotificationDispatcher();
            $action_label = wp_strip_all_tags($actionName);

            foreach ($channels as $ch) {
                $channel = sanitize_key((string) $ch);
                if (!in_array($channel, ['email', 'telegram', 'slack'], true)) {
                    continue;
                }

                $sent = $dispatcher->sendNow($channel, [
                    'title'  => __('Custom hook test notification', 'notification-hub'),
                    'body'   => sprintf(
                        __('The hook "%s" was manually triggered from the Hooks page.', 'notification-hub'),
                        $action_label
                    ),
                    'source' => 'hook_test',
                    'type'   => 'channel_test',
                    'link'   => admin_url('admin.php?page=nh-hooks'),
                    'cta_label' => __('Open Hooks Page', 'notification-hub'),
                    'context' => [
                        'action' => $action_label,
                        'admin_link' => admin_url('admin.php?page=nh-hooks'),
                    ],
                ]);

                if (!$sent) {
                    $ok = false;
                }
            }
        } catch (\Throwable $e) {
            $ok = false;
        }

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_tested=' . ($ok ? '1' : '0')));
        exit;
    }
}

