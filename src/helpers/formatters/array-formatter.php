<?php
/**
 * Array Formatter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers\Formatters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Array_Formatter {

	public static function to_comma_list( array $items ): string {
		return implode( ', ', $items );
	}

	public static function to_html_list( array $items, string $type = 'ul' ): string {
		if ( empty( $items ) ) {
			return '';
		}

		$list = '<' . $type . '>';

		foreach ( $items as $item ) {
			$list .= '<li>' . esc_html( $item ) . '</li>';
		}

		$list .= '</' . $type . '>';

		return $list;
	}

	public static function pluck( array $array, string $key ) {
		return wp_list_pluck( $array, $key );
	}

	public static function group_by( array $array, string $key ): array {
		$result = array();

		foreach ( $array as $item ) {
			if ( isset( $item[ $key ] ) ) {
				$result[ $item[ $key ] ][] = $item;
			}
		}

		return $result;
	}
}
