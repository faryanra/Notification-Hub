<?php
/**
 * Mark As Read Route
 *
 * AJAX handler for marking notifications as read.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mark_As_Read {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'wp_ajax_nh_mark_as_read', array( $this, 'handle' ) );
	}

	public function handle() {
		check_ajax_referer( 'nh_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Invalid ID' ), 400 );
		}

		$db = $this->container->get_svc( 'db' );

		if ( ! $db || ! method_exists( $db, 'update_status' ) ) {
			wp_send_json_error( array( 'message' => 'Service unavailable' ), 500 );
		}

		$db->update_status( $id, 1 );

		wp_send_json_success( array( 'id' => $id ) );
	}
}
