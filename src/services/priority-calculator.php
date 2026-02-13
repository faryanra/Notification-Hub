<?php
/**
 * Priority Calculator Service
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Priority Calculator
 */
class Priority_Calculator {

	public function calculate( $type ) {
		$priorities = array(
			'woocommerce_order' => 10,
			'woocommerce_stock' => 8,
			'user_registered'   => 5,
			'comment'           => 3,
			'custom_hook'       => 1,
		);

		return $priorities[ $type ] ?? 1;
	}
}
