<?php
/**
 * Cache Utility
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cache {

	private const PREFIX = 'nh_';
	private const GROUP = 'notification_hub';

	public static function get( string $key, $default = null ) {
		$value = wp_cache_get( self::PREFIX . $key, self::GROUP );

		if ( $value === false ) {
			return $default;
		}

		return $value;
	}

	public static function set( string $key, $value, int $expiration = 3600 ): bool {
		return wp_cache_set( self::PREFIX . $key, $value, self::GROUP, $expiration );
	}

	public static function delete( string $key ): bool {
		return wp_cache_delete( self::PREFIX . $key, self::GROUP );
	}

	public static function flush(): bool {
		return wp_cache_flush();
	}

	public static function remember( string $key, callable $callback, int $expiration = 3600 ) {
		$cached = self::get( $key );

		if ( $cached !== null ) {
			return $cached;
		}

		$value = call_user_func( $callback );

		self::set( $key, $value, $expiration );

		return $value;
	}
}
