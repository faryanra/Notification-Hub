<?php
/**
 * Test Custom Hook Route
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
 * Test Custom Hook
 */
class Test_Custom_Hook {

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

		$id = absint( $_POST['id'] ?? 0 );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid hook ID', 'notification-hub' ) ) );
		}

		$hook = $this->repo->get( $id );

		if ( ! $hook ) {
			wp_send_json_error( array( 'message' => __( 'Hook not found', 'notification-hub' ) ) );
		}

		// Trigger the hook
		do_action( $hook->hook_name );

		wp_send_json_success(
			array(
				'message' => sprintf(
					__( 'Hook "%s" triggered successfully', 'notification-hub' ),
					$hook->hook_name
				),
			)
		);
	}
}
