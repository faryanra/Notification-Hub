<?php
// NH v1.3.0 — REST API (Hook Test Endpoint)

if (!defined('ABSPATH')) exit;

class NH_REST_API {

    public function __construct() {
        // NH v1.3.0 — Register REST routes
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * NH v1.3.0 — Register REST endpoints
     */
    public function register_routes() {
        register_rest_route('nh/v1', '/test-trigger/(?P<id>\d+)', [
            'methods'  => 'POST',
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
            'callback' => [$this, 'handle_test_trigger'],
            'args' => [
                'id' => [
                    'validate_callback' => 'is_numeric'
                ]
            ]
        ]);
    }

    /**
     * NH v1.3.0 — Handle REST request: trigger hook by ID
     */
    public function handle_test_trigger(WP_REST_Request $req) {
        $id = intval($req['id']);
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nh_hooks WHERE id=%d", $id) );
        if (!$row) {
            return new WP_REST_Response(['ok'=>false, 'msg'=>'not found'], 404);
        }

        // Fire the custom hook
        do_action($row->action_name, [
            'test'    => true,
            'source'  => 'rest_test',
            'message' => 'Test triggered via REST API',
            'context' => ['hook_id' => $row->id]
        ]);

        return new WP_REST_Response(['ok'=>true, 'msg'=>'triggered'], 200);
    }
}
