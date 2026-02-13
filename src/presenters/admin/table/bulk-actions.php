<?php
/**
 * Table Bulk Actions
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bulk_Actions {

	public static function get_bulk_actions() {
		return array(
			'mark_read'   => __( 'Mark as Read', 'notification-hub' ),
			'mark_unread' => __( 'Mark as Unread', 'notification-hub' ),
			'delete'      => __( 'Delete', 'notification-hub' ),
		);
	}

	public static function process_bulk_action( $action, $ids ) {
		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;

		$ids = array_map( 'absint', $ids );
		$ids_placeholder = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		switch ( $action ) {
			case 'mark_read':
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}nh_notifications SET status = 'read' WHERE id IN ({$ids_placeholder})",
						...$ids
					)
				);
				break;

			case 'mark_unread':
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}nh_notifications SET status = 'unread' WHERE id IN ({$ids_placeholder})",
						...$ids
					)
				);
				break;

			case 'delete':
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}nh_notifications WHERE id IN ({$ids_placeholder})",
						...$ids
					)
				);
				break;
		}
	}
}
