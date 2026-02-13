<?php
/**
 * HTML Formatter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Formatters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HTML_Formatter {

	public static function badge( string $text, string $type = 'default' ): string {
		$class = 'nh-badge nh-badge--' . sanitize_html_class( $type );
		return sprintf( '<span class="%s">%s</span>', $class, esc_html( $text ) );
	}

	public static function status_badge( int $status ): string {
		if ( $status === 1 ) {
			return self::badge( __( 'Read', 'notification-hub' ), 'success' );
		}

		return self::badge( __( 'Unread', 'notification-hub' ), 'warning' );
	}

	public static function link( string $url, string $text, array $attrs = array() ): string {
		$attributes = '';

		foreach ( $attrs as $key => $value ) {
			$attributes .= sprintf( ' %s="%s"', sanitize_key( $key ), esc_attr( $value ) );
		}

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$attributes,
			esc_html( $text )
		);
	}

	public static function strip_tags( string $html, string $allowed_tags = '' ): string {
		return wp_strip_all_tags( $html, (bool) $allowed_tags );
	}
}
