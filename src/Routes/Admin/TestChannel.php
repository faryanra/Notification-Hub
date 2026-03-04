<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Repositories\SettingsRepository;
use NotificationHub\Services\ServiceFactory;

/**
 * Admin-post: test a notification channel.
 *
 * @since 1.7.2
 */
final class TestChannel {
    public function handle(): void {
        $this->doHandle(true);
    }

    /**
     * Legacy compat for old links in license box (without nonce).
     */
    public function handleLegacy(): void {
        $this->doHandle(false);
    }

    private function doHandle(bool $requireNonce): void {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }

        if ($requireNonce) {
            check_admin_referer('nh_test_channel');
        }

        $channel = isset($_GET['channel']) ? sanitize_key(wp_unslash($_GET['channel'])) : '';
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
        $allowed = ['email', 'telegram', 'slack'];

        if (!in_array($channel, $allowed, true)) {
            $channel = 'email';
        }

        $payload = [
            'title' => __('Test Notification', 'notification-hub'),
            'body' => __('This is a test message from Notification Hub.', 'notification-hub'),
            'source' => 'test',
            'type' => 'test',
            'context' => [
                'actor' => wp_get_current_user() ? wp_get_current_user()->user_login : '',
            ],
        ];

        if ($channel === 'email') {
            $general = (new SettingsRepository())->getGeneral();
            $to = !empty($general['email_to']) ? (string) $general['email_to'] : (string) get_option('admin_email');
            if (is_email($to)) {
                $payload['to'] = $to;
            }
        }

        $dispatcher = ServiceFactory::makeNotificationDispatcher();
        $ok = $dispatcher->sendNow($channel, $payload);

        $args = [
            'page' => 'nh_settings',
            'tab' => $tab,
            'nh_test' => $channel,
            'success' => $ok ? '1' : '0',
        ];

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }
}
