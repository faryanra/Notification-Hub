<?php
/**
 * License Option Store
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Option_Store {

	public static function get_license_key() {
		return get_option( 'nh_license_key', '' );
	}

	public static function set_license_key( $key ) {
		update_option( 'nh_license_key', $key );
	}

	public static function delete_license_key() {
		delete_option( 'nh_license_key' );
	}

	public static function get_license_status() {
		return get_option( 'nh_license_status', 'inactive' );
	}

	public static function set_license_status( $status ) {
		update_option( 'nh_license_status', $status );
	}
}
