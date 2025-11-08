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

        register_rest_route('nh/v1', '/notifications', [
            'methods'  => 'GET',
            'permission_callback' => function() {
                return current_user_can('read');
            },
            'callback' => function(WP_REST_Request $req) {
                global $wpdb;
                $table = $wpdb->prefix.'nh_notifications';

                $since   = sanitize_text_field($req->get_param('since'));
                $status  = sanitize_text_field($req->get_param('status'));
                $source  = sanitize_text_field($req->get_param('source'));
                $type    = sanitize_text_field($req->get_param('type'));

                $where = ['1=1'];
                $args  = [];

                if ($since) { $where[] = 'created_at > %s'; $args[] = $since; }
                if ($status !== '' && $status !== null && $status !== 'all') { $where[] = 'status = %d'; $args[] = (int)$status; }
                if ($source) { $where[] = 'source = %s'; $args[] = $source; }
                if ($type) { $where[] = 'type = %s'; $args[] = $type; }

                $where_sql = implode(' AND ', $where);
                $sql = "SELECT id,title,message,source,type,status,created_at,read_at FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT 50";
                $rows = $wpdb->get_results( $wpdb->prepare($sql, ...$args) );

                return new WP_REST_Response(['ok'=>true,'data'=>$rows], 200);
            }
        ]);
    }

    public function handle_test_trigger(WP_REST_Request $req) {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

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
