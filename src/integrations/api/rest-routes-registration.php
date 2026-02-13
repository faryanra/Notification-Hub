<?php
/**
 * REST API Routes Registration
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Api;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST Routes Registration
 */
class Rest_Routes_Registration implements Integration_Interface {

	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'notification-hub/v1',
			'/notifications',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_notifications' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'notification-hub/v1',
			'/notifications/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_notification' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	public function get_notifications( $request ) {
		global $wpdb;

		$notifications = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}nh_notifications ORDER BY created_at DESC LIMIT 20"
		);

		return rest_ensure_response( $notifications );
	}

	public function get_notification( $request ) {
		global $wpdb;

		$id = absint( $request['id'] );

		$notification = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}nh_notifications WHERE id = %d",
				$id
			)
		);

		if ( ! $notification ) {
			return new \WP_Error( 'not_found', __( 'Notification not found', 'notification-hub' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $notification );
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}
}
