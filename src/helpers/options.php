<?php
/**
 * Options Helper
 *
 * Wrapper for WordPress options API with caching.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options Helper Class
 */
class Options {

	/**
	 * Get option value.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		return get_option( $key, $default );
	}

	/**
	 * Set option value.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public static function set( $key, $value ) {
		return update_option( $key, $value );
	}

	/**
	 * Delete option.
	 *
	 * @param string $key Option key.
	 * @return bool
	 */
	public static function delete( $key ) {
		return delete_option( $key );
	}

	/**
	 * Check if option exists.
	 *
	 * @param string $key Option key.
	 * @return bool
	 */
	public static function exists( $key ) {
		return false !== get_option( $key, false );
	}
}
