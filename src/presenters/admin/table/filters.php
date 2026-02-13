<?php
/**
 * Table Filters
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Filters {

	public static function get_status_filters() {
		return array(
			'all'    => __( 'All', 'notification-hub' ),
			'unread' => __( 'Unread', 'notification-hub' ),
			'read'   => __( 'Read', 'notification-hub' ),
		);
	}

	public static function get_type_filters() {
		return array(
			'all'               => __( 'All Types', 'notification-hub' ),
			'comment'           => __( 'Comment', 'notification-hub' ),
			'post_status'       => __( 'Post Status', 'notification-hub' ),
			'user_registered'   => __( 'User Registered', 'notification-hub' ),
			'woocommerce_order' => __( 'WooCommerce Order', 'notification-hub' ),
			'woocommerce_stock' => __( 'Low Stock', 'notification-hub' ),
			'cf7_submission'    => __( 'CF7 Submission', 'notification-hub' ),
			'custom_hook'       => __( 'Custom Hook', 'notification-hub' ),
		);
	}
}
