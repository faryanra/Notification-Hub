<?php
/**
 * JSON Validator
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JSON_Validator {

	public static function validate( string $json ): bool {
		json_decode( $json );
		return json_last_error() === JSON_ERROR_NONE;
	}

	public static function decode( string $json, bool $assoc = true ) {
		$data = json_decode( $json, $assoc );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return null;
		}

		return $data;
	}

	public static function encode( $data ): string {
		return wp_json_encode( $data );
	}
}
