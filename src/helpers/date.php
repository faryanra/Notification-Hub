<?php
/**
 * Date Helper
 *
 * Date/time utilities.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Date {

	public static function format( $timestamp, $format = 'Y-m-d H:i:s' ) {
		return gmdate( $format, $timestamp );
	}

	public static function now() {
		return current_time( 'timestamp' );
	}
}
