<?php
namespace NotificationHub\Routes\Api;


if (!defined('ABSPATH')) {
    exit;
}

use WP_REST_Request;
use WP_REST_Response;

/**
 * REST route: GET /nh/v1/notifications
 *
 * Used by dashboard live refresh poll.
 *
 * @since 1.0.0
 */
final class GetNotifications {
    public function handle(WP_REST_Request $req): WP_REST_Response {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_notifications';

        $params = $req->get_params();
        $where  = ['1=1'];
        $args   = [];

        $since  = !empty($params['since']) ? sanitize_text_field((string) $params['since']) : null;
        $status = (isset($params['status']) && $params['status'] !== null) ? sanitize_text_field((string) $params['status']) : null;
        $source = !empty($params['source']) ? sanitize_text_field((string) $params['source']) : null;
        $type   = !empty($params['type']) ? sanitize_text_field((string) $params['type']) : null;

        $min_priority   = isset($params['min_priority']) ? (int) $params['min_priority'] : null;
        $only_important = !empty($params['only_important']);
        $tags_filter    = [];

        if (!empty($params['tags'])) {
            $tags_filter = array_filter(array_map('trim', explode(',', (string) $params['tags'])));
        }

        if ($since) {
            $where[] = 'created_at > %s';
            $args[]  = $since;
        }

        if ($status !== '' && $status !== null && $status !== 'all') {
            $where[] = 'status = %d';
            $args[]  = (int) $status;
        }

        if ($source) {
            $where[] = 'source = %s';
            $args[]  = $source;
        }

        if ($type) {
            $where[] = 'type = %s';
            $args[]  = $type;
        }

        if ($min_priority !== null) {
            $where[] = 'priority >= %d';
            $args[]  = $min_priority;
        }

        if ($only_important) {
            $where[] = 'status = 3';
        }

        foreach ($tags_filter as $tag) {
            $where[] = 'tags LIKE %s';
            $args[]  = '%"' . $wpdb->esc_like((string) $tag) . '"%';
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where);

        $limit  = isset($params['limit']) ? absint($params['limit']) : 50;
        $offset = isset($params['offset']) ? absint($params['offset']) : 0;

        $args[] = $limit;
        $args[] = $offset;

        $query = $wpdb->prepare(
            "SELECT id, source, type, title, message, status, priority, tags, created_at, read_at
             FROM {$table} {$where_sql}
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d",
            ...$args
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $rows = $wpdb->get_results($query, ARRAY_A);

        return new WP_REST_Response(
            [
                'ok'    => true,
                'count' => is_array($rows) ? count($rows) : 0,
                'data'  => is_array($rows) ? $rows : [],
            ],
            200
        );
    }
}

