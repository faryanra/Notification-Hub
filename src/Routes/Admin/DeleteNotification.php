<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Helpers\Security;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Admin AJAX route: Delete notification.
 *
 * @since 1.0.0
 */
final class DeleteNotification {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
        if (!$id) {
            wp_send_json_error(['message' => esc_html__('Invalid ID.', 'notification-hub')], 400);
        }

        $repo = new NotificationsRepository();
        $ok   = $repo->deleteById($id);

        if (!$ok) {
            wp_send_json_error(['message' => esc_html__('Delete failed.', 'notification-hub')], 500);
        }

        wp_send_json_success(['id' => $id]);
    }
}

