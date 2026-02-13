<?php
/**
 * Contact Form 7 Active Conditional
 *
 * Checks if Contact Form 7 is active.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact_Form_7_Active Conditional Class
 */
class Contact_Form_7_Active implements Conditional {

	/**
	 * Check if Contact Form 7 is active.
	 *
	 * @return bool
	 */
	public function is_met() {
		return class_exists( 'WPCF7' );
	}
}
