<?php
// Webhook receiver (skeleton)

if (!defined('ABSPATH')) exit;

class NH_Webhook {

    protected $r;

    public function __construct($registry = null) {
        $this->r = $registry;
    }

    public function init() {
        add_action('rest_api_init', [$this, 'register_inbound']);
    }

    public function register_inbound() {
        register_rest_route('nh/v1', '/inbound', [
            'methods'  => 'POST',
            'permission_callback' => '__return_true', // public endpoint for external services
            'callback' => [$this, 'receive'],
        ]);
    }

    public function receive(WP_REST_Request $req) {
        $body = $req->get_json_params();

        if (empty($body['message'])) {
            return new WP_REST_Response(['ok' => false, 'msg' => 'Missing payload'], 400);
        }

        $registry = class_exists('NH_Core_Registry') ? NH_Core_Registry::get() : null;
        $notifier = $registry ? $registry->get_svc('notifier') : null;
        if (!$notifier) {
            return new WP_REST_Response(['ok' => false, 'msg' => 'Notifier missing'], 500);
        }

        // Example future logic: route inbound messages to dashboard/log
        $notifier->send([
            'channel' => 'email',
            'title'   => 'Inbound Webhook',
            'body'    => $body['message'],
            'source'  => 'webhook'
        ]);

        return new WP_REST_Response(['ok' => true, 'msg' => 'Webhook received'], 200);
    }
}
