<?php
/**
 * NH_Webhook
 *
 * Webhook receiver endpoint (inbound) for Notification Hub.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Webhook {

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
    }

    /**
     * Register hooks.
     *
     * @since 1.6.2
     * @return void
     */
    public function init() {
        add_action('rest_api_init', [$this, 'register_inbound']);
    }

    /**
     * Register inbound webhook endpoint.
     *
     * @since 1.6.2
     * @return void
     */
    public function register_inbound() {
        register_rest_route('nh/v1', '/inbound', [
            'methods'  => 'POST',
            // Public endpoint for external services.
            'permission_callback' => '__return_true',
            'callback' => [$this, 'receive'],
        ]);
    }

    /**
     * Receive inbound webhook request.
     *
     * @since 1.6.2
     * @param WP_REST_Request $req Request object.
     * @return WP_REST_Response
     */
    public function receive(WP_REST_Request $req) {
        $body = $req->get_json_params();

        if (empty($body['message'])) {
            return new WP_REST_Response(
                [
                    'ok'  => false,
                    'msg' => esc_html__('Missing payload.', 'notification-hub'),
                ],
                400
            );
        }

        $registry = class_exists('NH_Core_Registry') ? NH_Core_Registry::get() : null;
        $notifier = $registry ? $registry->get_svc('notifier') : null;

        if (!$notifier) {
            return new WP_REST_Response(
                [
                    'ok'  => false,
                    'msg' => esc_html__('Notifier missing.', 'notification-hub'),
                ],
                500
            );
        }

        // Route inbound messages to notification delivery.
        $notifier->send(
            [
                'channel' => 'email',
                'title'   => esc_html__('Inbound Webhook', 'notification-hub'),
                'body'    => (string) $body['message'],
                'source'  => 'webhook',
            ]
        );

        return new WP_REST_Response(
            [
                'ok'  => true,
                'msg' => esc_html__('Webhook received.', 'notification-hub'),
            ],
            200
        );
    }
}