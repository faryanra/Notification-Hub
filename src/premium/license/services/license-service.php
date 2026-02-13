<?php
/**
 * License Service
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Services;

use Notification_Hub\Premium\License\Http\License_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License_Service {

	private $client;

	public function __construct( License_Client $client ) {
		$this->client = $client;
	}

	public function validate_license( $license_key ) {
		$is_valid = $this->client->validate( $license_key );

		if ( $is_valid ) {
			update_option( 'nh_license_status', 'active' );
		} else {
			update_option( 'nh_license_status', 'invalid' );
		}

		return $is_valid;
	}

	public function activate_license( $license_key ) {
		return $this->client->activate( $license_key );
	}

	public function deactivate_license( $license_key ) {
		return $this->client->deactivate( $license_key );
	}
}
