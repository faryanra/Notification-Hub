<?php
/**
 * Cron Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron Conditional
 */
class Cron implements Conditional {

	/**
	 * Check if doing cron.
	 *
	 * @return bool
	 */
	public function is_met() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
