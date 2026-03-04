<?php

namespace NotificationHub\Routes\Api;

use NotificationHub\Repositories\NotificationsRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST: Update a notification.
 *
 * @since 1.7.2
 */
final class UpdateNotification {
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

        $action = sanitize_key((string) ($request->get_param('action') ?? ''));

        $ok = false;
        switch ($action) {
            case 'read':
                $ok = $repo->markRead($id);
                break;
            case 'unread':
                $ok = $repo->markUnread($id);
                break;
            case 'important':
                $ok = $repo->markImportant($id);
                break;
            case 'unimportant':
                $ok = $repo->unmarkImportant($id);
                break;
            default:
                return new WP_REST_Response(['message' => 'Invalid action'], 400);
        }

        if (!$ok) {
            return new WP_REST_Response(['message' => 'Update failed'], 500);
        }

        return new WP_REST_Response([
            'success' => true,
            'id'      => $id,
            'action'  => $action,
        ], 200);
    }
}
