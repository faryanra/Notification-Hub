<?php
/**
 * License Domain Policy
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Policy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Domain_Policy {

	public static function is_allowed_domain( $domain ) {
		$allowed_domains = get_option( 'nh_license_allowed_domains', array() );

		return in_array( $domain, $allowed_domains, true );
	}
}
