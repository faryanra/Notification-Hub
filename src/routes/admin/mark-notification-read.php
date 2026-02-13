<?php
/**
 * Mark Notification Read Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mark Notification Read
 */
class Mark_Notification_Read {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle() {
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'nh_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'notification-hub' ) ) );
		}

		if ( ! Security::can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'notification-hub' ) ) );
		}

		$id = absint( $_POST['id'] ?? 0 );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid notification ID', 'notification-hub' ) ) );
		}

		$result = $this->repo->update( $id, array( 'status' => 'read' ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Notification marked as read', 'notification-hub' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update notification', 'notification-hub' ) ) );
		}
	}
}
