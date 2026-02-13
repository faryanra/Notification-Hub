<?php
/**
 * Admin Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Conditional
 */
class Admin implements Conditional {

	/**
	 * Check if in admin.
	 *
	 * @return bool
	 */
	public function is_met() {
		return is_admin();
	}
}
