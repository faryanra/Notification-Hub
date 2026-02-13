<?php
/**
 * Sanitization Helper
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitization Helper
 */
class Sanitization {

	public static function email( $email ) {
		return sanitize_email( $email );
	}

	public static function url( $url ) {
		return esc_url_raw( $url );
	}

	public static function int( $value ) {
		return absint( $value );
	}

	public static function array_text( $array ) {
		return array_map( 'sanitize_text_field', $array );
	}
}
