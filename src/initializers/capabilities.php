<?php
/**
 * Capabilities Initializer
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capabilities
 */
class Capabilities {

	public static function run() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$role->add_cap( 'manage_notifications' );
		}
	}
}
