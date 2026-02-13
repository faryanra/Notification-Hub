<?php
/**
 * Admin Conditional
 *
 * Checks if current context is admin area.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Conditional Class
 */
class Admin implements Conditional {

	/**
	 * Check if we're in admin area.
	 *
	 * @return bool
	 */
	public function is_met() {
		return is_admin();
	}
}
