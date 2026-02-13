<?php
/**
 * Options Helper
 *
 * Wrapper for get_option/update_option with caching.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options {

	private static $cache = array();

	public static function get( $key, $default = '' ) {
		if ( isset( self::$cache[ $key ] ) ) {
			return self::$cache[ $key ];
		}

		$value = get_option( $key, $default );
		self::$cache[ $key ] = $value;

		return $value;
	}

	public static function set( $key, $value ) {
		update_option( $key, $value );
		self::$cache[ $key ] = $value;
	}
}
