<?php
/**
 * Premium Active Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Premium Active Conditional
 */
class Premium_Active implements Conditional {

	/**
	 * Check if Premium is active.
	 *
	 * @return bool
	 */
	public function is_met() {
		return defined( 'NH_PRO_ACTIVE' ) && NH_PRO_ACTIVE;
	}
}
