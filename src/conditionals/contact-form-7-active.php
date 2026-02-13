<?php
/**
 * Contact Form 7 Active Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Contact_Form_7_Active implements Conditional {
	public function is_met(): bool {
		return class_exists( 'WPCF7' );
	}
}
