<?php
/**
 * Security Helper
 *
 * Security utilities for capability checks, nonces, and sanitization.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Helper Class
 */
class Security {

	/**
	 * Ensure current user can manage plugin settings.
	 *
	 * @return void
	 */
	public static function ensure_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'notification-hub' ) );
		}
	}

	/**
	 * Verify a request nonce.
	 *
	 * @param string $action_base Action base string.
	 * @param int    $id          Optional numeric context.
	 * @return void
	 */
	public static function verify_nonce( $action_base, $id = 0 ) {
		$nonce = isset( $_REQUEST['_wpnonce'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) )
			: '';

		$action = $id ? ( $action_base . '_' . $id ) : $action_base;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die(
				esc_html__(
					'Invalid request (nonce). Please refresh and try again.',
					'notification-hub'
				)
			);
		}
	}

	/**
	 * Output nonce field for use in forms.
	 *
	 * @param string $action_base Action base string.
	 * @param int    $id          Optional numeric context.
	 * @return void
	 */
	public static function nonce_field( $action_base, $id = 0 ) {
		$action = $id ? ( $action_base . '_' . $id ) : $action_base;
		wp_nonce_field( $action );
	}

	/**
	 * Sanitize and normalize list of selected channels.
	 *
	 * @param mixed $maybe_channels Channels array from request.
	 * @return array<string> Clean channel slugs.
	 */
	public static function sanitize_channels( $maybe_channels ) {
		if ( ! is_array( $maybe_channels ) ) {
			return array();
		}

		$clean = array();
		foreach ( $maybe_channels as $ch ) {
			$s = sanitize_text_field( (string) $ch );
			if ( '' !== $s ) {
				$clean[] = $s;
			}
		}

		return $clean;
	}

	/**
	 * Validate a custom hook action name.
	 *
	 * @param string $raw Raw action name.
	 * @return string Sanitized action name.
	 */
	public static function validate_action_name( $raw ) {
		$raw = sanitize_text_field( $raw );
		return preg_replace( '/[^a-zA-Z0-9_\-\.]/', '_', $raw );
	}

	/**
	 * Safely pull an integer from POST or GET.
	 *
	 * @param string $key Request key.
	 * @return int Parsed integer.
	 */
	public static function request_int( $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			return (int) wp_unslash( $_POST[ $key ] );
		}
		if ( isset( $_GET[ $key ] ) ) {
			return (int) wp_unslash( $_GET[ $key ] );
		}
		return 0;
	}
}
