<?php
/**
 * Delete Notification REST Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Api;

use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delete_Notification {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle( $request ) {
		$id = absint( $request['id'] );

		if ( ! $id ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid notification ID', 'notification-hub' ), array( 'status' => 400 ) );
		}

		$result = $this->repo->delete( $id );

		if ( ! $result ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete notification', 'notification-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}
}
