<?php
/**
 * Webhook REST Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Api;

use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Webhook {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle( $request ) {
		$title   = $request->get_param( 'title' ) ?? '';
		$message = $request->get_param( 'message' ) ?? '';
		$type    = $request->get_param( 'type' ) ?? 'webhook';

		if ( empty( $title ) ) {
			return new \WP_Error( 'missing_title', __( 'Title is required', 'notification-hub' ), array( 'status' => 400 ) );
		}

		$notification_id = $this->repo->create(
			array(
				'title'   => sanitize_text_field( $title ),
				'message' => sanitize_textarea_field( $message ),
				'type'    => sanitize_text_field( $type ),
				'status'  => 'unread',
			)
		);

		if ( ! $notification_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create notification', 'notification-hub' ), array( 'status' => 500 ) );
		}

		do_action( 'nh_notification_created', $notification_id, $type );

		return rest_ensure_response(
			array(
				'success' => true,
				'id'      => $notification_id,
			)
		);
	}
}
