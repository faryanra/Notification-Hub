<?php
/**
 * Sanitization Helper
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sanitization {

	public static function log( $msg, string $level = 'info' ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$level = strtoupper( sanitize_key( $level ) );
		if ( $level === '' ) {
			$level = 'INFO';
		}

		if ( ! is_string( $msg ) ) {
			$msg = wp_json_encode( $msg );
		}

		$prefix = sprintf( '[%s][NH]', $level );

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $prefix . ' ' . (string) $msg );
	}

	public static function json_pretty( $data ): string {
		return (string) wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}

	public static function truncate( $text, int $limit = 200 ): string {
		$text = (string) $text;
		$limit = max( 0, $limit );

		if ( $limit === 0 ) {
			return '';
		}

		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
			return ( mb_strlen( $text, 'UTF-8' ) > $limit )
				? ( mb_substr( $text, 0, $limit, 'UTF-8' ) . '…' )
				: $text;
		}

		return ( strlen( $text ) > $limit )
			? ( substr( $text, 0, $limit ) . '…' )
			: $text;
	}
}
