<?php
/**
 * NH_Admin_Hooks
 *
 * Admin-only actions for hooks module (channel test only).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Admin_Hooks {

    /**
     * Register admin-post handlers.
     *
     * @since 1.6.2
     * @return void
     */
    public static function init(): void {
        add_action('admin_post_nh_test_channel', [__CLASS__, 'test_channel']);
        // NOTE: Hook CRUD + hook test are handled by NH_Custom_Hooks to avoid duplicate handlers.
    }

    /**
     * Test a specific channel.
     *
     * @since 1.6.2
     * @return void
     */
    public static function test_channel(): void {
        NH_Security::ensure_cap();
        NH_Security::verify_nonce('nh_test_channel');

        $channel = isset($_GET['channel']) ? sanitize_key(wp_unslash($_GET['channel'])) : '';

        $registry = class_exists('NHCoreRegistry') && method_exists('NHCoreRegistry', 'get')
            ? NHCoreRegistry::get()
            : null;

        $notifier = $registry && method_exists($registry, 'get_svc')
            ? $registry->get_svc('notifier')
            : null;

        if (!$notifier || !method_exists($notifier, 'send')) {
            wp_die(esc_html__('Notifier is not available.', 'notification-hub'));
        }

        $notifier->send($channel, [
            'title'  => __('Notification Hub Test', 'notification-hub'),
            'body'   => __('This is a test notification.', 'notification-hub'),
            'source' => 'admin_test',
            'type'   => 'test',
        ]);

        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page=nh-settings'));
        exit;
    }
}
