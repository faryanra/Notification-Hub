<?php
// NH v1.2.0 — Test Controller (Safe redirect + error catch)

if (!defined('ABSPATH')) exit;

class NH_Test_Controller {

    public static function init() {
        add_action('admin_post_nh_test_channel', [__CLASS__, 'handle']);
    }

    public static function handle() {
        try {
            if (!current_user_can('manage_options')) {
                wp_die(__('Access denied', 'notification-hub'));
            }

            check_admin_referer('nh_test_channel');

            $channel  = sanitize_text_field($_GET['channel'] ?? '');
            $registry = NH_Core_Registry::get();
            $notifier = $registry->get_svc('notifier');

            if (!$notifier) {
                error_log('❌ NH_Test_Controller: Notifier not found.');
                wp_die(__('Notifier service not available.', 'notification-hub'));
            }

            $ok = $notifier->send([
                'channel' => $channel,
                'title'   => '🔔 Notification Hub Test',
                'body'    => 'This is a test message from Notification Hub.',
                'source'  => 'test'
            ]);

            // ✅ جلوگیری از خروجی اضافی قبل از ریدایرکت
            if (ob_get_length()) {
                @ob_end_clean();
            }

            // ✅ گام جدید — حفظ تب فعال در هنگام بازگشت
            $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

            $redirect = add_query_arg([
                'page'    => 'nh_settings',
                'tab'     => $tab,
                'nh_test' => $channel,
                'success' => $ok ? '1' : '0'
            ], admin_url('admin.php'));

            wp_safe_redirect($redirect);
            exit;

        } catch (Throwable $e) {
            error_log('❌ NH_Test_Controller Exception: ' . $e->getMessage());
            wp_die('Test failed: ' . $e->getMessage());
        }
    }
}

NH_Test_Controller::init();
