<?php
/**
 * Delete Notification Route
 *
 * AJAX handler for deleting notifications.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delete_Notification {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'wp_ajax_nh_delete_notification', array( $this, 'handle' ) );
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

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

		wp_send_json_success( array( 'id' => $id ) );
	}
}
