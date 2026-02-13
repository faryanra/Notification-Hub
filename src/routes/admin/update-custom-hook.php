<?php
/**
 * Update Custom Hook Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

use Notification_Hub\Repositories\Custom_Hooks;
use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update Custom Hook
 */
class Update_Custom_Hook {

	/**
	 * Custom hooks repository.
	 *
	 * @var Custom_Hooks
	 */
	private $repo;

	/**
	 * Constructor.
	 *
	 * @param Custom_Hooks $repo Custom hooks repository.
	 */
	public function __construct( Custom_Hooks $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function handle() {
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'nh_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'notification-hub' ) ) );
		}

		if ( ! Security::can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'notification-hub' ) ) );
		}

		$id        = absint( $_POST['id'] ?? 0 );
		$hook_name = Security::sanitize_text( $_POST['hook_name'] ?? '' );
		$title     = Security::sanitize_text( $_POST['title'] ?? '' );
		$message   = Security::sanitize_textarea( $_POST['message'] ?? '' );
		$status    = Security::sanitize_text( $_POST['status'] ?? 'active' );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid hook ID', 'notification-hub' ) ) );
		}

		$result = $this->repo->update(
			$id,
			array(
				'hook_name' => $hook_name,
				'title'     => $title,
				'message'   => $message,
				'status'    => $status,
			)
		);

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Custom hook updated successfully', 'notification-hub' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update custom hook', 'notification-hub' ) ) );
		}
	}
}
