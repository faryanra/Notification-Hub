<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Helpers\Security;

/**
 * Admin AJAX route: Trigger dispatcher test.
 *
 * @since 1.7.2
 */
final class DispatcherTest {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $action = isset($_POST['action_name']) ? sanitize_text_field(wp_unslash($_POST['action_name'])) : '';
        if ($action === '') {
            wp_send_json_error(['message' => esc_html__('Invalid action.', 'notification-hub')], 400);
        }

        /**
         * Fire a custom hook action.
         *
         * This is used for testing CustomHooksLoader wiring.
         */
        do_action($action, [
            'test'    => true,
            'source'  => 'admin_test',
            'message' => esc_html__('Triggered via dispatcher test.', 'notification-hub'),
        ]);

        wp_send_json_success(['ok' => true]);
    }
}
