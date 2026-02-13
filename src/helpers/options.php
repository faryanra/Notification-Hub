<?php
/**
 * Options Helper
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options Helper
 */
class Options {

	/**
	 * Get option.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		return get_option( 'nh_' . $key, $default );
	}

	/**
	 * Set option.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public static function set( $key, $value ) {
		return update_option( 'nh_' . $key, $value );
	}

	/**
	 * Delete option.
	 *
	 * @param string $key Option key.
	 * @return bool
	 */
	public static function delete( $key ) {
		return delete_option( 'nh_' . $key );
	}
}
