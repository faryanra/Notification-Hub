<?php
/**
 * Premium Active Conditional
 *
 * Checks if Premium version is active.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Premium_Active Conditional Class
 */
class Premium_Active implements Conditional {

	/**
	 * Check if Premium is active.
	 *
	 * @return bool
	 */
	public function is_met() {
		return defined( 'NH_PRO_VERSION' );
	}
}
