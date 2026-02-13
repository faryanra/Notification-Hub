<?php
/**
 * Capability Checker
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capability_Checker {

	public static function can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function can_edit_posts(): bool {
		return current_user_can( 'edit_posts' );
	}

	public static function can_manage_nh(): bool {
		return current_user_can( 'manage_notification_hub' );
	}

	public static function require_capability( string $capability = 'manage_options' ): void {
		if ( ! current_user_can( $capability ) ) {
			wp_die(
				esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ),
				esc_html__( 'Permission Denied', 'notification-hub' ),
				array( 'response' => 403 )
			);
		}
	}
}
