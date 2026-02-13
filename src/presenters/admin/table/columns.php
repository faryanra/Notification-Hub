<?php
/**
 * Table Columns
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Columns {

	public static function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'title'      => __( 'Title', 'notification-hub' ),
			'type'       => __( 'Type', 'notification-hub' ),
			'status'     => __( 'Status', 'notification-hub' ),
			'created_at' => __( 'Date', 'notification-hub' ),
			'actions'    => __( 'Actions', 'notification-hub' ),
		);
	}

	public static function get_sortable_columns() {
		return array(
			'title'      => array( 'title', false ),
			'type'       => array( 'type', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', true ),
		);
	}
}
