<?php
/**
 * License HTTP Client
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Http;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License_Client {

	private $server_url;

	public function __construct( $server_url = '' ) {
		$this->server_url = $server_url ?: get_option( 'nh_license_server', '' );
	}

	public function validate( $license_key ) {
		$response = wp_remote_post(
			$this->server_url . '/validate',
			array(
				'body' => array(
					'license_key' => $license_key,
					'domain'      => home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['valid'] ?? false;
	}

	public function activate( $license_key ) {
		$response = wp_remote_post(
			$this->server_url . '/activate',
			array(
				'body' => array(
					'license_key' => $license_key,
					'domain'      => home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['success'] ?? false;
	}

	public function deactivate( $license_key ) {
		$response = wp_remote_post(
			$this->server_url . '/deactivate',
			array(
				'body' => array(
					'license_key' => $license_key,
					'domain'      => home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['success'] ?? false;
	}
}
