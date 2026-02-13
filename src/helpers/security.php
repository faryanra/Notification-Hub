<?php
/**
 * Security Helper
 *
 * Security helpers for admin actions.
 * (Refactored from NH_Security)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Security {

	public static function ensure_cap(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'notification-hub' ) );
		}
	}

	public static function verify_nonce( string $action_base, int $id = 0 ): void {
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

	public static function nonce_field( string $action_base, int $id = 0 ): void {
		$action = $id ? ( $action_base . '_' . $id ) : $action_base;
		wp_nonce_field( $action );
	}

	public static function sanitize_channels( $maybe_channels ): array {
		if ( ! is_array( $maybe_channels ) ) {
			return array();
		}

		$clean = array();
		foreach ( $maybe_channels as $ch ) {
			$s = sanitize_text_field( (string) $ch );
			if ( $s !== '' ) {
				$clean[] = $s;
			}
		}

		return $clean;
	}

	public static function validate_action_name( string $raw ): string {
		$raw = sanitize_text_field( $raw );
		return preg_replace( '/[^a-zA-Z0-9_\-\.]/', '_', $raw );
	}

	public static function request_int( string $key ): int {
		if ( isset( $_POST[ $key ] ) ) {
			return (int) wp_unslash( $_POST[ $key ] );
		}
		if ( isset( $_GET[ $key ] ) ) {
			return (int) wp_unslash( $_GET[ $key ] );
		}
		return 0;
	}

	public static function anti_tamper_light(): bool {
		return true;
	}
}
