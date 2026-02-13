<?php
/**
 * Test Channel Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test Channel
 */
class Test_Channel {

	public function handle() {
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'nh_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'notification-hub' ) ) );
		}

		if ( ! Security::can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'notification-hub' ) ) );
		}

		$channel = Security::sanitize_text( $_POST['channel'] ?? '' );

		if ( empty( $channel ) ) {
			wp_send_json_error( array( 'message' => __( 'Channel is required', 'notification-hub' ) ) );
		}

		// Test notification
		do_action( 'nh_test_channel_' . $channel );

		wp_send_json_success( array( 'message' => sprintf( __( 'Test notification sent via %s', 'notification-hub' ), $channel ) ) );
	}
}
