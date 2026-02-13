<?php
/**
 * Ajax Conditional
 *
 * Checks if current request is AJAX.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax Conditional Class
 */
class Ajax implements Conditional {

	/**
	 * Check if we're handling AJAX request.
	 *
	 * @return bool
	 */
	public function is_met() {
		return wp_doing_ajax();
	}
}
