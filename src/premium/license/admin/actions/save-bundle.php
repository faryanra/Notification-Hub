<?php
/**
 * Save License Bundle Action
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Admin\Actions;

use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_Bundle {

	public function handle() {
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'nh_license_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'notification-hub' ) ) );
		}

		if ( ! Security::can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'notification-hub' ) ) );
		}

		$bundle = Security::sanitize_text( $_POST['bundle'] ?? '' );

		update_option( 'nh_license_bundle', $bundle );

		wp_send_json_success( array( 'message' => __( 'License bundle saved successfully', 'notification-hub' ) ) );
	}
}
