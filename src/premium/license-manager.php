<?php
/**
 * License Manager
 *
 * Premium license validation.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License_Manager {

	private static $instance = null;

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function is_active(): bool {
		$license = get_option( 'nh_license_key', '' );
		$status  = get_option( 'nh_license_status', '' );

		return ! empty( $license ) && $status === 'active';
	}

	public function can( string $feature ): bool {
		if ( ! $this->is_active() ) {
			return false;
		}

		$allowed_features = array( 'telegram', 'slack', 'advanced_filters', 'api_access' );

		return in_array( $feature, $allowed_features, true );
	}

	public function activate( string $license_key ): array {
		$license_key = sanitize_text_field( $license_key );

		if ( empty( $license_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'License key is required', 'notification-hub' ),
			);
		}

		// TODO: Call license server API
		$response = wp_remote_post(
			'https://notificationhub.example.com/api/license/activate',
			array(
				'body' => array(
					'license_key' => $license_key,
					'site_url'    => home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['success'] ) && $data['success'] ) {
			update_option( 'nh_license_key', $license_key );
			update_option( 'nh_license_status', 'active' );

			return array(
				'success' => true,
				'message' => __( 'License activated successfully', 'notification-hub' ),
			);
		}

		return array(
			'success' => false,
			'message' => $data['message'] ?? __( 'License activation failed', 'notification-hub' ),
		);
	}

	public function deactivate(): array {
		delete_option( 'nh_license_key' );
		delete_option( 'nh_license_status' );

		return array(
			'success' => true,
			'message' => __( 'License deactivated', 'notification-hub' ),
		);
	}
}
