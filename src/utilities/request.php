<?php
/**
 * Request Utility
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Request {

	public static function get( string $key, $default = null ) {
		return isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : $default;
	}

	public static function post( string $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $default;
	}

	public static function input( string $key, $default = null ) {
		$value = self::post( $key );

		if ( $value === null ) {
			$value = self::get( $key );
		}

		return $value ?? $default;
	}

	public static function is_ajax(): bool {
		return wp_doing_ajax();
	}

	public static function is_admin(): bool {
		return is_admin();
	}

	public static function is_rest(): bool {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	public static function get_ip(): string {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}
}
