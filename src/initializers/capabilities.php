<?php
/**
 * Capabilities
 *
 * Add custom capabilities on activation.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {

	public static function run() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$role->add_cap( 'manage_notification_hub' );
		}
	}
}
