<?php
/**
 * Notifications Repository
 *
 * Database CRUD for notifications table.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications Repository
 */
class Notifications {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'nh_notifications';
	}

	/**
	 * Create notification.
	 *
	 * @param array $data Notification data.
	 * @return int|false Notification ID or false.
	 */
	public function create( array $data ) {
		global $wpdb;

		$defaults = array(
			'title'      => '',
			'message'    => '',
			'type'       => 'general',
			'status'     => 'unread',
			'created_at' => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		$result = $wpdb->insert(
			$this->table,
			$data,
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get notification by ID.
	 *
	 * @param int $id Notification ID.
	 * @return object|null
	 */
	public function get( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Get all notifications.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function get_all( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'limit'  => 20,
			'offset' => 0,
			'status' => '',
			'type'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$where = array();

		if ( ! empty( $args['status'] ) ) {
			$where[] = $wpdb->prepare( 'status = %s', $args['status'] );
		}

		if ( ! empty( $args['type'] ) ) {
			$where[] = $wpdb->prepare( 'type = %s', $args['type'] );
		}

		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$sql = "SELECT * FROM {$this->table} {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";

		return $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				$args['limit'],
				$args['offset']
			)
		);
	}

	/**
	 * Update notification.
	 *
	 * @param int   $id   Notification ID.
	 * @param array $data Update data.
	 * @return bool
	 */
	public function update( $id, array $data ) {
		global $wpdb;

		$result = $wpdb->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Delete notification.
	 *
	 * @param int $id Notification ID.
	 * @return bool
	 */
	public function delete( $id ) {
		global $wpdb;

		$result = $wpdb->delete(
			$this->table,
			array( 'id' => $id ),
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Count notifications.
	 *
	 * @param array $args Query arguments.
	 * @return int
	 */
	public function count( array $args = array() ) {
		global $wpdb;

		$where = array();

		if ( ! empty( $args['status'] ) ) {
			$where[] = $wpdb->prepare( 'status = %s', $args['status'] );
		}

		if ( ! empty( $args['type'] ) ) {
			$where[] = $wpdb->prepare( 'type = %s', $args['type'] );
		}

		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$sql = "SELECT COUNT(*) FROM {$this->table} {$where_clause}";

		return (int) $wpdb->get_var( $sql );
	}
}
