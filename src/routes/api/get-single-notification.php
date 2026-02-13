<?php
/**
 * Get Single Notification REST Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Api;

use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_Single_Notification {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle( $request ) {
		$id = absint( $request['id'] );

		if ( ! $id ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid notification ID', 'notification-hub' ), array( 'status' => 400 ) );
		}

		$notification = $this->repo->get( $id );

		if ( ! $notification ) {
			return new \WP_Error( 'not_found', __( 'Notification not found', 'notification-hub' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $notification );
	}
}
