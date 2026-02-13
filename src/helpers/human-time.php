<?php
/**
 * Human Time Helper
 *
 * Human-readable time formatting.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Human_Time Helper Class
 */
class Human_Time {

	/**
	 * Convert MySQL datetime to human-readable relative time.
	 *
	 * @param string $mysql_date MySQL datetime string.
	 * @return string
	 */
	public static function ago( $mysql_date ) {
		if ( empty( $mysql_date ) || '0000-00-00 00:00:00' === $mysql_date ) {
			return esc_html__( 'Never', 'notification-hub' );
		}

		$timestamp = strtotime( $mysql_date );
		$now       = current_time( 'timestamp' );
		$diff      = $now - $timestamp;

		if ( $diff < 60 ) {
			return esc_html__( 'Just now', 'notification-hub' );
		}

		if ( $diff < 3600 ) {
			$mins = floor( $diff / 60 );
			return sprintf(
				/* translators: %d: number of minutes */
				_n( '%d minute ago', '%d minutes ago', $mins, 'notification-hub' ),
				$mins
			);
		}

		if ( $diff < 86400 ) {
			$hours = floor( $diff / 3600 );
			return sprintf(
				/* translators: %d: number of hours */
				_n( '%d hour ago', '%d hours ago', $hours, 'notification-hub' ),
				$hours
			);
		}

		if ( $diff < 604800 ) {
			$days = floor( $diff / 86400 );
			return sprintf(
				/* translators: %d: number of days */
				_n( '%d day ago', '%d days ago', $days, 'notification-hub' ),
				$days
			);
		}

		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
	}
}
