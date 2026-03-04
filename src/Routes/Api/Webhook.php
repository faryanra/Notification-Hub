<?php

namespace NotificationHub\Routes\Api;

use NotificationHub\Repositories\NotificationsRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST: Webhook endpoint to ingest a notification.
 *
 * Body example (JSON):
 * {"source":"external","type":"webhook","title":"...","message":"...","tags":["a"],"context":{}}
 *
 * @since 1.7.2
 */
final class Webhook {
    public function handle(WP_REST_Request $request): WP_REST_Response {
        // Simple shared-secret auth (optional).
        $expected = (string) get_option('nh_webhook_secret', '');
        if ($expected !== '') {
            $got = (string) $request->get_header('x-nh-secret');
            if (!hash_equals($expected, $got)) {
                return new WP_REST_Response(['message' => 'Unauthorized'], 401);
            }
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            return new WP_REST_Response(['message' => 'Invalid JSON body'], 400);
        }

        $repo = new NotificationsRepository();
        $id = $repo->insert($body);
        if ($id <= 0) {
            return new WP_REST_Response(['message' => 'Insert failed'], 400);
        }

        return new WP_REST_Response(['success' => true, 'id' => $id], 201);
    }
}
