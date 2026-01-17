<?php
/**
 * NH_REST_API
 *
 * REST API endpoints for Notification Hub (admin dashboard feed + test trigger).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_REST_API {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var mixed
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry = null) {
        $this->r = $registry;
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST routes.
     *
     * @since 1.6.2
     * @return void
     */
    public function register_routes() {

        /**
         * POST /test-trigger/{id}
         */
        register_rest_route('nh/v1', '/test-trigger/(?P<id>\d+)', [
            'methods'             => 'POST',
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
            'callback'            => [$this, 'handle_test_trigger'],
        ]);

        /**
         * GET /notifications
         */
        register_rest_route('nh/v1', '/notifications', [
            'methods'             => 'GET',

            // Admin dashboard endpoint.
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },

            'args' => [
                'since' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'status' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'source' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'type' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'min_priority' => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'tags' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'only_important' => [
                    'type'              => 'boolean',
                    'sanitize_callback' => static function ($v) {
                        return (bool) $v;
                    },
                ],
                'limit' => [
                    'type'              => 'integer',
                    'default'           => 50,
                    'sanitize_callback' => 'absint',
                ],
                'offset' => [
                    'type'              => 'integer',
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],

            'callback' => [$this, 'get_notifications'],
        ]);
    }

    /**
     * GET /notifications
     *
     * @since 1.6.2
     * @param WP_REST_Request $req Request object.
     * @return WP_REST_Response
     */
    public function get_notifications(WP_REST_Request $req) {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_notifications';

        $params = $req->get_params();
        $where  = ['1=1'];
        $args   = [];

        $since  = !empty($params['since']) ? sanitize_text_field((string) $params['since']) : null;
        $status = !empty($params['status']) ? sanitize_text_field((string) $params['status']) : null;
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

        $rows = $wpdb->get_results($query, ARRAY_A);

        return new WP_REST_Response(
            [
                'ok'    => true,
                'count' => count($rows),
                'data'  => $rows,
            ],
            200
        );
    }

    /**
     * POST /test-trigger/{id}
     *
     * @since 1.6.2
     * @param WP_REST_Request $req Request object.
     * @return WP_REST_Response
     */
    public function handle_test_trigger(WP_REST_Request $req) {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_hooks';

        $exists = $wpdb->get_var(
            $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))
        );

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

        $registry = class_exists('NH_Core_Registry') ? NH_Core_Registry::get() : null;
        $notifier = $registry ? $registry->get_svc('notifier') : null;

        if (!$notifier) {
            return new WP_REST_Response(
                [
                    'ok'  => false,
                    'msg' => esc_html__('Notifier not available.', 'notification-hub'),
                ],
                500
            );
        }

        try {
            do_action(
                $row->action_name,
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