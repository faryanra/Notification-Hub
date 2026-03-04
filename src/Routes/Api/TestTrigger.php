<?php

namespace NotificationHub\Routes\Api;

use Throwable;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST route: POST /nh/v1/test-trigger/{id}
 *
 * Triggers a saved hook action for testing, matching legacy behavior.
 *
 * @since 1.7.2
 */
final class TestTrigger {
    public function handle(WP_REST_Request $req): WP_REST_Response {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_hooks';

        // Ensure hooks table exists.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));

        if (!$exists) {
            return new WP_REST_Response(
                [
                    'ok'  => false,
                    'msg' => esc_html__('Database table missing.', 'notification-hub'),
                ],
                500
            );
        }

        $id  = (int) $req['id'];
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));

        if (!$row) {
            return new WP_REST_Response(
                [
                    'ok'  => false,
                    'msg' => esc_html__('Hook not found.', 'notification-hub'),
                ],
                404
            );
        }

        // Legacy executed the hook and relied on existing hook handlers.
        try {
            do_action(
                (string) $row->action_name,
                [
                    'test'    => true,
                    'source'  => 'rest_test',
                    'message' => esc_html__('Triggered via REST API.', 'notification-hub'),
                ]
            );

            return new WP_REST_Response(
                [
                    'ok'  => true,
                    'msg' => esc_html__('Hook triggered.', 'notification-hub'),
                ],
                200
            );
        } catch (Throwable $e) {
            return new WP_REST_Response(
                [
                    'ok'  => false,
                    'msg' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
