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
        if (!class_exists('NH_Security')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        NH_Security::verify_nonce('nh_test_channel');

        $channel = isset($_GET['channel']) ? sanitize_key(wp_unslash($_GET['channel'])) : '';

        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';
        if ($tab !== 'general' && $tab !== 'pro') {
            $tab = '';
        }

        if ($channel === '') {
            wp_safe_redirect(add_query_arg(['page' => 'nh_settings', 'tab' => $tab ?: 'general', 'success' => 0, 'nh_test' => ''], admin_url('admin.php')));
            exit;
        }

        $registry = class_exists('NH_Core_Registry') && method_exists('NH_Core_Registry', 'get')
            ? NH_Core_Registry::get()
            : null;

        // NOTE: registry getter is get_svc() (not get()).
        $notifier = $registry && method_exists($registry, 'get_svc')
            ? $registry->get_svc('notifier')
            : null;

        // Fallback: in case boot order prevents registry population, instantiate notifier directly.
        if ((!$notifier || !method_exists($notifier, 'send')) && class_exists('NH_Notifier')) {
            try {
                $notifier = new NH_Notifier($registry ?: NH_Core_Registry::get());
            } catch (Throwable $e) {
                $notifier = null;
            }
        }

        if (!$notifier || !method_exists($notifier, 'send')) {
            // Avoid white-screen: redirect back to settings with failure notice.
            wp_safe_redirect(
                add_query_arg(
                    [
                        'page'    => 'nh_settings',
                        'tab'     => $tab ?: 'general',
                        'success' => 0,
                        'nh_test' => $channel,
                    ],
                    admin_url('admin.php')
                )
            );
            exit;
        }

        $ok = true;

        try {
            $result = $notifier->send($channel, [
                'title'  => esc_html__('Notification Hub Test', 'notification-hub'),
                'body'   => esc_html__('This is a test notification.', 'notification-hub'),
                'source' => 'admin_test',
                'type'   => 'test',
            ]);

            if ($result === false) {
                $ok = false;
            }
        } catch (Throwable $e) {
            $ok = false;

            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('Notification Hub: channel test failed: ' . $e->getMessage());
            }
        }

        // Redirect back to Settings with a visible notice (templates/settings.php reads success + nh_test).
        wp_safe_redirect(
            add_query_arg(
                [
                    'page'    => 'nh_settings',
                    'tab'     => $tab ?: 'general',
                    'success' => $ok ? 1 : 0,
                    'nh_test' => $channel,
                ],
                admin_url('admin.php')
            )
        );
        exit;
    }
}