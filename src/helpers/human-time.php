<?php
/**
 * Human Time Helper
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Human Time Helper
 */
class Human_Time {

	/**
	 * Convert timestamp to human-readable format.
	 *
	 * @param string $datetime Date/time string.
	 * @return string
	 */
	public static function ago( $datetime ) {
		return human_time_diff( strtotime( $datetime ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'notification-hub' );
	}
}
