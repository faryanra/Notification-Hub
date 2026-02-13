<?php
/**
 * Logger Utility
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {

	public static function log( string $message, string $level = 'info' ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$formatted = sprintf(
			'[%s] [%s] %s',
			current_time( 'Y-m-d H:i:s' ),
			strtoupper( $level ),
			$message
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $formatted );
	}

	public static function error( string $message ): void {
		self::log( $message, 'error' );
	}

	public static function warning( string $message ): void {
		self::log( $message, 'warning' );
	}

	public static function info( string $message ): void {
		self::log( $message, 'info' );
	}

	public static function debug( string $message ): void {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			self::log( $message, 'debug' );
		}
	}
}
