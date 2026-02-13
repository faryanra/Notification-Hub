<?php
/**
 * WooCommerce Active Conditional
 *
 * Checks if WooCommerce is active.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce_Active Conditional Class
 */
class WooCommerce_Active implements Conditional {

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_met() {
		return class_exists( 'WooCommerce' );
	}
}
