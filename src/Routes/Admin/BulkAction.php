<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Helpers\Security;

/**
 * Admin AJAX route: Bulk action.
 *
 * @since 1.0.0
 */
final class BulkAction {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $action = isset($_POST['bulk_action']) ? sanitize_key(wp_unslash($_POST['bulk_action'])) : '';
        $ids    = isset($_POST['ids']) ? (array) wp_unslash($_POST['ids']) : [];
        $ids    = array_values(array_filter(array_map('absint', $ids)));

        if ($action === '' || empty($ids)) {
            wp_send_json_error(['message' => esc_html__('Invalid request.', 'notification-hub')], 400);
        }

        // Keep legacy behavior (table bulk actions were DB-direct).
        if (!class_exists('NH_Table_Bulk_Actions')) {
            $file = NH_SRC_DIR . 'Presenters/Admin/Table/BulkActions.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }

        if (!class_exists('NH_Table_Bulk_Actions') || !method_exists('NH_Table_Bulk_Actions', 'process')) {
            wp_send_json_error(['message' => esc_html__('Bulk handler missing.', 'notification-hub')], 500);
        }

        $affected = \NH_Table_Bulk_Actions::process($action, $ids);

        if ($affected === false) {
            wp_send_json_error(['message' => esc_html__('Bulk action failed.', 'notification-hub')], 500);
        }

        wp_send_json_success(['affected' => (int) $affected]);
    }
}

