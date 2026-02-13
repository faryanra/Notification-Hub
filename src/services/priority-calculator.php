<?php
/**
 * Priority Calculator Service
 *
 * (Extracted from NH_Notifier_Queue::calculate_priority)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Priority_Calculator {

	public function calculate( string $source, string $type, $explicit_priority ): int {
		if ( $explicit_priority !== null && $explicit_priority !== '' ) {
			return max( 0, min( 100, (int) $explicit_priority ) );
		}

		// WooCommerce orders
		if ( $source !== '' && function_exists( 'str_contains' ) && ( str_contains( $source, 'woocommerce' ) || str_contains( $type, 'order' ) ) ) {
			return 80;
		}

		// Security alerts
		if ( function_exists( 'str_contains' ) ) {
			$is_security = (
				( $source !== '' && ( str_contains( $source, 'security' ) || str_contains( $source, 'wordfence' ) ) ) ||
				( $type !== '' && ( str_contains( $type, 'security' ) || str_contains( $type, 'error' ) ) )
			);
			if ( $is_security ) {
				return 90;
			}
		}

		// Comments
		if ( $type !== '' && function_exists( 'str_contains' ) && str_contains( $type, 'comment' ) ) {
			return 60;
		}

		// Forms (CF7)
		if ( function_exists( 'str_contains' ) ) {
			$is_forms = (
				( $source !== '' && str_contains( $source, 'cf7' ) ) ||
				( $type !== '' && ( str_contains( $type, 'form' ) || str_contains( $type, 'cf7' ) ) )
			);
			if ( $is_forms ) {
				return 55;
			}
		}

		return 50;
	}
}
