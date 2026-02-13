<?php
/**
 * Cron Schedules
 *
 * Register cron jobs on activation.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cron_Schedules {

	public static function run() {
		if ( ! wp_next_scheduled( 'nh_cron_cleanup' ) ) {
			wp_schedule_event( time() + 3600, 'daily', 'nh_cron_cleanup' );
		}

		if ( ! wp_next_scheduled( 'nh_process_queue' ) ) {
			wp_schedule_event( time(), 'hourly', 'nh_process_queue' );
		}
	}
}
