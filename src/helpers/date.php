<?php
/**
 * Date Helper
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date Helper
 */
class Date {

	/**
	 * Get current time in MySQL format.
	 *
	 * @return string
	 */
	public static function now() {
		return current_time( 'mysql' );
	}

	/**
	 * Format date.
	 *
	 * @param string $date   Date string.
	 * @param string $format Date format.
	 * @return string
	 */
	public static function format( $date, $format = 'Y-m-d H:i:s' ) {
		return date_i18n( $format, strtotime( $date ) );
	}
}
