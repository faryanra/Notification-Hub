<?php
/**
 * Table Query Builder
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Query {

	public static function build_query( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status'  => '',
			'type'    => '',
			'orderby' => 'created_at',
			'order'   => 'DESC',
			'limit'   => 20,
			'offset'  => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$where = array();

		if ( ! empty( $args['status'] ) && $args['status'] !== 'all' ) {
			$where[] = $wpdb->prepare( 'status = %s', $args['status'] );
		}

		if ( ! empty( $args['type'] ) && $args['type'] !== 'all' ) {
			$where[] = $wpdb->prepare( 'type = %s', $args['type'] );
		}

		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );

		$sql = "SELECT * FROM {$wpdb->prefix}nh_notifications {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";

		return $wpdb->prepare( $sql, $args['limit'], $args['offset'] );
	}

	public static function count_query( $args = array() ) {
		global $wpdb;

		$where = array();

		if ( ! empty( $args['status'] ) && $args['status'] !== 'all' ) {
			$where[] = $wpdb->prepare( 'status = %s', $args['status'] );
		}

		if ( ! empty( $args['type'] ) && $args['type'] !== 'all' ) {
			$where[] = $wpdb->prepare( 'type = %s', $args['type'] );
		}

		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		return "SELECT COUNT(*) FROM {$wpdb->prefix}nh_notifications {$where_clause}";
	}
}
