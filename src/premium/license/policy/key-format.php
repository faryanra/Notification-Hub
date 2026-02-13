<?php
/**
 * License Key Format Policy
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Policy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Key_Format {

	public static function validate_format( $license_key ) {
		return preg_match( '/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key );
	}
}
