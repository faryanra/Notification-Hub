<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\SettingsRepository;
use NotificationHub\Services\ServiceFactory;

/**
 * Admin-post: test a notification channel.
 *
 * @since 1.0.0
 */
final class TestChannel {
    public function handle(): void {
        $this->doHandle();
    }

    private function doHandle(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Forbidden', 'notification-hub'), 403);
        }

        check_admin_referer('nh_test_channel');

        $channel = isset($_GET['channel']) ? sanitize_key(wp_unslash($_GET['channel'])) : '';
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
        $allowed = ['email', 'telegram', 'slack'];

        if (!in_array($channel, $allowed, true)) {
            $channel = 'email';
        }

        $payload = [
            'title' => __('Notification Hub channel test', 'notification-hub'),
            'body' => __('This is a test notification from your website. If you can read this, the selected channel is working correctly.', 'notification-hub'),
            'source' => 'test',
            'type' => 'channel_test',
            'link' => admin_url('admin.php?page=nh-dashboard'),
            'cta_label' => __('Open Notification Hub', 'notification-hub'),
            'context' => [
                'actor' => wp_get_current_user() ? wp_get_current_user()->user_login : '',
                'admin_link' => admin_url('admin.php?page=nh-dashboard'),
                'channel' => $channel,
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
        $result = $dispatcher->sendNowDetailed($channel, $payload);
        $ok = !empty($result['ok']);

        $args = [
            'page' => 'nh_settings',
            'tab' => $tab,
            'nh_test' => $channel,
            'success' => $ok ? '1' : '0',
        ];
        if (!$ok) {
            $error = isset($result['error']) ? sanitize_text_field((string) $result['error']) : 'send_failed';
            $code = isset($result['http_code']) ? (int) $result['http_code'] : 0;
            $args['nh_test_error'] = rawurlencode(substr($error, 0, 160));
            if ($code > 0) {
                $args['nh_test_http'] = $code;
            }
        }

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }
}

