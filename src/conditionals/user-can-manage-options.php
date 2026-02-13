<?php
/**
 * User Can Manage Options Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User_Can_Manage_Options implements Conditional {
	public function is_met(): bool {
		return current_user_can( 'manage_options' );
	}
}
