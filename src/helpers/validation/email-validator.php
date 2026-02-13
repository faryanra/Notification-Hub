<?php
/**
 * Email Validator
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Validator {

	public static function validate( string $email ): bool {
		return is_email( $email ) !== false;
	}

	public static function validate_multiple( string $emails, string $separator = ',' ): array {
		$emails = explode( $separator, $emails );
		$valid  = array();

		foreach ( $emails as $email ) {
			$email = trim( $email );
			if ( self::validate( $email ) ) {
				$valid[] = $email;
			}
		}

		return $valid;
	}
}
