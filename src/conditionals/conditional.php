<?php
/**
 * Conditional Interface
 *
 * All conditionals must implement this interface.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conditional Interface
 */
interface Conditional {

	/**
	 * Check if the condition is met.
	 *
	 * @return bool
	 */
	public function is_met();
}
