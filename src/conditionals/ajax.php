<?php
/**
 * AJAX Conditional
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Conditional
 */
class Ajax implements Conditional {

	/**
	 * Check if doing AJAX.
	 *
	 * @return bool
	 */
	public function is_met() {
		return wp_doing_ajax();
	}
}
