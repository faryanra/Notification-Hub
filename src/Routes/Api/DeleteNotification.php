<?php
namespace NotificationHub\Routes\Api;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\NotificationsRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST: Delete a notification.
 *
 * @since 1.0.0
 */
final class DeleteNotification {
    public function handle(WP_REST_Request $request): WP_REST_Response {
        $id = absint($request->get_param('id'));
        if ($id <= 0) {
            return new WP_REST_Response(['message' => __('Invalid ID.', 'notification-hub')], 400);
        }

        $repo = new NotificationsRepository();
        $row = $repo->getById($id);
        if (!$row) {
            return new WP_REST_Response(['message' => __('Notification not found.', 'notification-hub')], 404);
        }

        $ok = $repo->deleteById($id);
        if (!$ok) {
            return new WP_REST_Response(['message' => __('Failed to delete notification.', 'notification-hub')], 500);
        }

        return new WP_REST_Response(['success' => true, 'id' => $id], 200);
    }
}

