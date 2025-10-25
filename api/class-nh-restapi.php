<?php
// REST API (safe activation)

if (!defined('ABSPATH')) exit;

class NH_REST_API {

    protected $r;

    public function __construct($registry = null) {
        $this->r = $registry;
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('nh/v1', '/test-trigger/(?P<id>\d+)', [
            'methods'  => 'POST',
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
            'callback' => [$this, 'handle_test_trigger'],
        ]);
    }

    public function handle_test_trigger(WP_REST_Request $req) {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        // 🧱 Check table existence
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table)));
        if (!$exists) {
            return new WP_REST_Response(['ok' => false, 'msg' => 'Database table missing'], 500);
        }

        $id = intval($req['id']);
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));
        if (!$row) {
            return new WP_REST_Response(['ok' => false, 'msg' => 'Hook not found'], 404);
        }

        $registry = class_exists('NH_Core_Registry') ? NH_Core_Registry::get() : null;
        $notifier = $registry ? $registry->get_svc('notifier') : null;
        if (!$notifier) {
            return new WP_REST_Response(['ok' => false, 'msg' => 'Notifier not available'], 500);
        }

        try {
            do_action($row->action_name, [
                'test'    => true,
                'source'  => 'rest_test',
                'message' => 'Triggered via REST API',
            ]);
            return new WP_REST_Response(['ok' => true, 'msg' => 'Hook triggered'], 200);
        } catch (Throwable $e) {
            return new WP_REST_Response(['ok' => false, 'msg' => $e->getMessage()], 500);
        }
    }
}
