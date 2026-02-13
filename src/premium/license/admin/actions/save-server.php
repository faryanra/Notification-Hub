<?php
/**
 * Save License Server Action
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Admin\Actions;

use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_Server {

	public function handle() {
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'nh_license_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'notification-hub' ) ) );
		}

		if ( ! Security::can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'notification-hub' ) ) );
		}

		$license_server = Security::sanitize_text( $_POST['license_server'] ?? '' );

		if ( empty( $license_server ) ) {
			wp_send_json_error( array( 'message' => __( 'License server is required', 'notification-hub' ) ) );
		}

		update_option( 'nh_license_server', $license_server );

		wp_send_json_success( array( 'message' => __( 'License server saved successfully', 'notification-hub' ) ) );
	}
}
