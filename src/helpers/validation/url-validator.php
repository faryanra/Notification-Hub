<?php
/**
 * URL Validator
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class URL_Validator {

	public static function validate( string $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	public static function is_https( string $url ): bool {
		return strpos( $url, 'https://' ) === 0;
	}

	public static function sanitize( string $url ): string {
		return esc_url_raw( $url );
	}
}
