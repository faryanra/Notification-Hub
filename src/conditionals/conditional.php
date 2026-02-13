<?php
/**
 * Conditional Interface
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Conditionals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Conditional {
	public function is_met(): bool;
}
