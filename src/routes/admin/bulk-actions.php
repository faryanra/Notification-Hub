<?php
/**
 * Bulk Actions Route
 *
 * AJAX handler for bulk operations.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bulk_Actions {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'wp_ajax_nh_bulk_action', array( $this, 'handle' ) );
	}

	public function handle() {
		check_ajax_referer( 'nh_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$action = isset( $_POST['action_type'] ) ? sanitize_key( $_POST['action_type'] ) : '';
		$ids    = isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ? array_map( 'intval', $_POST['ids'] ) : array();

		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => 'No items selected' ), 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		switch ( $action ) {
			case 'mark_read':
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$table} SET status = 1, updated_at = %s WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
						current_time( 'mysql' ),
						...$ids
					)
				);
				break;

			case 'delete':
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$table} WHERE id IN (" . implode( ',', array_fill( 0, count( $ids ), '%d' ) ) . ')',
						...$ids
					)
				);
				break;

			default:
				wp_send_json_error( array( 'message' => 'Invalid action' ), 400 );
		}

		wp_send_json_success( array( 'count' => count( $ids ) ) );
	}
}
