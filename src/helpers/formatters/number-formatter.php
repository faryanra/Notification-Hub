<?php
/**
 * Number Formatter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Formatters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Number_Formatter {

	public static function format( $number, int $decimals = 0 ): string {
		return number_format_i18n( (float) $number, $decimals );
	}

	public static function abbreviate( $number ): string {
		$number = (float) $number;

		if ( $number >= 1000000 ) {
			return round( $number / 1000000, 1 ) . 'M';
		}

		if ( $number >= 1000 ) {
			return round( $number / 1000, 1 ) . 'K';
		}

		return (string) $number;
	}

	public static function percentage( float $value, float $total, int $decimals = 1 ): string {
		if ( $total === 0.0 ) {
			return '0%';
		}

		return number_format( ( $value / $total ) * 100, $decimals ) . '%';
	}
}
