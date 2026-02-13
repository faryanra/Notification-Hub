<?php
/**
 * Settings Repository
 *
 * Wrapper for WordPress options.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public static function get( $key, $default = '' ) {
		return get_option( $key, $default );
	}

	public static function set( $key, $value ) {
		update_option( $key, $value );
	}

	public static function delete( $key ) {
		delete_option( $key );
	}
}
