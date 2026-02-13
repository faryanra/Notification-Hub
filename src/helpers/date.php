<?php
/**
 * Date Helper
 *
 * Date utilities.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date Helper Class
 */
class Date {

	/**
	 * Get current MySQL datetime.
	 *
	 * @return string
	 */
	public static function now() {
		return current_time( 'mysql' );
	}

	/**
	 * Convert MySQL datetime to timestamp.
	 *
	 * @param string $mysql_date MySQL datetime string.
	 * @return int
	 */
	public static function to_timestamp( $mysql_date ) {
		return strtotime( $mysql_date );
	}

	/**
	 * Format date for display.
	 *
	 * @param string $mysql_date MySQL datetime string.
	 * @param string $format     PHP date format.
	 * @return string
	 */
	public static function format( $mysql_date, $format = 'Y-m-d H:i:s' ) {
		$timestamp = self::to_timestamp( $mysql_date );
		return date_i18n( $format, $timestamp );
	}
}
