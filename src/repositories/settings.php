<?php
/**
 * Settings Repository
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Repository
 */
class Settings {

	private $cache = array();

	public function get( $key, $default = '' ) {
		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}

		$value = get_option( 'nh_' . $key, $default );

		$this->cache[ $key ] = $value;

		return $value;
	}

	public function set( $key, $value ) {
		$this->cache[ $key ] = $value;

		return update_option( 'nh_' . $key, $value );
	}

	public function delete( $key ) {
		unset( $this->cache[ $key ] );

		return delete_option( 'nh_' . $key );
	}
}
