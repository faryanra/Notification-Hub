<?php
/**
 * String Formatter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Formatters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class String_Formatter {

	public static function truncate( string $text, int $length = 100, string $suffix = '...' ): string {
		if ( mb_strlen( $text ) <= $length ) {
			return $text;
		}

		return mb_substr( $text, 0, $length ) . $suffix;
	}

	public static function excerpt( string $text, int $words = 55 ): string {
		return wp_trim_words( $text, $words, '...' );
	}

	public static function slug( string $text ): string {
		return sanitize_title( $text );
	}

	public static function capitalize( string $text ): string {
		return ucwords( $text );
	}
}
