<?php
/**
 * Create Custom Hook Route
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
 * Create Custom Hook
 */
class Create_Custom_Hook {

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

		$hook_name = Security::sanitize_text( $_POST['hook_name'] ?? '' );
		$title     = Security::sanitize_text( $_POST['title'] ?? '' );
		$message   = Security::sanitize_textarea( $_POST['message'] ?? '' );

		if ( empty( $hook_name ) || empty( $title ) ) {
			wp_send_json_error( array( 'message' => __( 'Hook name and title are required', 'notification-hub' ) ) );
		}

		$id = $this->repo->create(
			array(
				'hook_name' => $hook_name,
				'title'     => $title,
				'message'   => $message,
				'status'    => 'active',
			)
		);

		if ( $id ) {
			wp_send_json_success(
				array(
					'message' => __( 'Custom hook created successfully', 'notification-hub' ),
					'id'      => $id,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create custom hook', 'notification-hub' ) ) );
		}
	}
}
