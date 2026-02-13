<?php
/**
 * Update Notification REST Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Api;

use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Update_Notification {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle( $request ) {
		$id     = absint( $request['id'] );
		$status = Security::sanitize_text( $request->get_param( 'status' ) ?? '' );

		if ( ! $id ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid notification ID', 'notification-hub' ), array( 'status' => 400 ) );
		}

		$result = $this->repo->update( $id, array( 'status' => $status ) );

		if ( ! $result ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update notification', 'notification-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}
}
