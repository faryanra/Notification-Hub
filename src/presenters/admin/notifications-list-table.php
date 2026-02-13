<?php
/**
 * Notifications List Table Presenter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications List Table
 */
class Notifications_List_Table extends \WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'notification',
				'plural'   => 'notifications',
				'ajax'     => false,
			)
		);
	}

	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'title'      => __( 'Title', 'notification-hub' ),
			'type'       => __( 'Type', 'notification-hub' ),
			'status'     => __( 'Status', 'notification-hub' ),
			'created_at' => __( 'Date', 'notification-hub' ),
		);
	}

	public function prepare_items() {
		global $wpdb;

		$per_page = 20;
		$paged    = $this->get_pagenum();

		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}nh_notifications ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				( $paged - 1 ) * $per_page
			)
		);

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}nh_notifications" );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	public function column_default( $item, $column_name ) {
		return $item->$column_name;
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="notification[]" value="%s" />', $item->id );
	}
}
