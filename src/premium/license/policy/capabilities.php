<?php
/**
 * License Capabilities Policy
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Policy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {

	public static function user_can_manage_license() {
		return current_user_can( 'manage_options' );
	}
}
