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

class Cron implements Conditional {
	public function is_met(): bool {
		return wp_doing_cron();
	}
}
