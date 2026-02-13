<?php
/**
 * Human Time Helper
 *
 * Human-friendly labels for sources and types.
 * (Refactored from NH_Human)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Human_Time {

	public static function source( string $source ): string {
		$map = array(
			'wp_core'     => 'WordPress',
			'woocommerce' => 'WooCommerce',
			'cf7'         => 'Contact Form 7',
			'hook'        => 'Custom Hook',
		);

		$source = sanitize_key( $source );
		return $map[ $source ] ?? ( $source !== '' ? $source : 'Unknown' );
	}

	public static function type( string $type ): string {
		$map = array(
			'comment_new'          => 'New Comment',
			'order_created'        => 'New Order',
			'low_stock'            => 'Low Stock',
			'form_sent'            => 'Form Submitted',
			'form_failed'          => 'Form Failed',
			'post_status_changed'  => 'Post Status Changed',
			'user_registered'      => 'New User',
		);

		$type = sanitize_key( $type );
		return $map[ $type ] ?? ( $type !== '' ? $type : 'Unknown' );
	}
}
