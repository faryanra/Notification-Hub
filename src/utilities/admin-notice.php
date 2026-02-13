<?php
/**
 * Admin Notice Utility
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Notice {

	public static function success( string $message ): void {
		self::render( $message, 'success' );
	}

	public static function error( string $message ): void {
		self::render( $message, 'error' );
	}

	public static function warning( string $message ): void {
		self::render( $message, 'warning' );
	}

	public static function info( string $message ): void {
		self::render( $message, 'info' );
	}

	private static function render( string $message, string $type ): void {
		add_action(
			'admin_notices',
			static function () use ( $message, $type ) {
				printf(
					'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
					esc_attr( $type ),
					esc_html( $message )
				);
			}
		);
	}

	public static function transient( string $key, string $message, string $type = 'success' ): void {
		set_transient( 'nh_notice_' . $key, array( 'message' => $message, 'type' => $type ), 60 );
	}

	public static function show_transient( string $key ): void {
		$notice = get_transient( 'nh_notice_' . $key );

		if ( $notice && is_array( $notice ) ) {
			self::render( $notice['message'], $notice['type'] ?? 'info' );
			delete_transient( 'nh_notice_' . $key );
		}
	}
}
