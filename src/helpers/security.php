<?php
/**
 * Security Helper
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Helper
 */
class Security {

	/**
	 * Verify nonce.
	 *
	 * @param string $nonce  Nonce value.
	 * @param string $action Nonce action.
	 * @return bool
	 */
	public static function verify_nonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Check capability.
	 *
	 * @param string $capability Capability name.
	 * @return bool
	 */
	public static function can( $capability ) {
		return current_user_can( $capability );
	}

	/**
	 * Sanitize text field.
	 *
	 * @param string $value Input value.
	 * @return string
	 */
	public static function sanitize_text( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize textarea.
	 *
	 * @param string $value Input value.
	 * @return string
	 */
	public static function sanitize_textarea( $value ) {
		return sanitize_textarea_field( $value );
	}
}
