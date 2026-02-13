<?php
/**
 * REST API Integration
 *
 * Registers REST API endpoints.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\API;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
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

		register_rest_route(
			'notification-hub/v1',
			'/notifications',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_notification' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	public function get_notifications( $request ) {
		$page     = $request->get_param( 'page' ) ?? 1;
		$per_page = $request->get_param( 'per_page' ) ?? 20;

		$repo  = new \Notification_Hub\Repositories\Notifications();
		$items = $repo->get_list( array(), (int) $page, (int) $per_page );

		return rest_ensure_response( $items );
	}

	public function get_notification( $request ) {
		$id = (int) $request->get_param( 'id' );

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		$item = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( ! $item ) {
			return new \WP_Error( 'not_found', 'Notification not found', array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	public function create_notification( $request ) {
		$source  = $request->get_param( 'source' ) ?? '';
		$type    = $request->get_param( 'type' ) ?? '';
		$title   = $request->get_param( 'title' ) ?? '';
		$message = $request->get_param( 'message' ) ?? '';

		if ( empty( $source ) || empty( $type ) || empty( $title ) || empty( $message ) ) {
			return new \WP_Error( 'missing_params', 'Missing required parameters', array( 'status' => 400 ) );
		}

		$repo = new \Notification_Hub\Repositories\Notifications();
		$id   = $repo->insert(
			array(
				'source'  => $source,
				'type'    => $type,
				'title'   => $title,
				'message' => $message,
			)
		);

		if ( ! $id ) {
			return new \WP_Error( 'create_failed', 'Failed to create notification', array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'id'      => $id,
				'message' => 'Notification created successfully',
			)
		);
	}
}
