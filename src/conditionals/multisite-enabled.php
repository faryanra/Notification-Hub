<?php
/**
 * Multisite Enabled Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Enabled
 */
class Multisite_Enabled implements Conditional {

	public function is_met() {
		return is_multisite();
	}
}
