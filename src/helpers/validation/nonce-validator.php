<?php
/**
 * Nonce Validator
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nonce_Validator {

	public static function verify( string $nonce, string $action = 'nh_admin' ): bool {
		return wp_verify_nonce( $nonce, $action ) !== false;
	}

	public static function create( string $action = 'nh_admin' ): string {
		return wp_create_nonce( $action );
	}

	public static function check_ajax( string $action = 'nh_admin' ): void {
		check_ajax_referer( $action, 'nonce' );
	}

	public static function check_admin( string $action = 'nh_admin' ): void {
		check_admin_referer( $action );
	}
}
