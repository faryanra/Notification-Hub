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

class Premium_Active implements Conditional {
	public function is_met(): bool {
		return defined( 'NH_PRO_ACTIVE' ) && NH_PRO_ACTIVE;
	}
}
