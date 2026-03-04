<?php

namespace NotificationHub\Routes\Api;

use NotificationHub\Repositories\NotificationsRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST: Delete a notification.
 *
 * @since 1.7.2
 */
final class DeleteNotification {
    public function handle(WP_REST_Request $request): WP_REST_Response {
        $id = absint($request->get_param('id'));
        if ($id <= 0) {
            return new WP_REST_Response(['message' => 'Invalid id'], 400);
        }

        $repo = new NotificationsRepository();
        $row = $repo->getById($id);
        if (!$row) {
            return new WP_REST_Response(['message' => 'Not found'], 404);
        }

        $ok = $repo->deleteById($id);
        if (!$ok) {
            return new WP_REST_Response(['message' => 'Delete failed'], 500);
        }

        return new WP_REST_Response(['success' => true, 'id' => $id], 200);
    }
}
