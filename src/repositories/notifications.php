<?php
/**
 * Notifications Repository
 *
 * CRUD for notifications table.
 * (Extracted from NH_Database)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifications {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'nh_notifications';
	}

	public function insert( array $data ): int {
		global $wpdb;

		$now = current_time( 'mysql' );

		$source  = isset( $data['source'] ) ? sanitize_text_field( (string) $data['source'] ) : '';
		$type    = isset( $data['type'] ) ? sanitize_text_field( (string) $data['type'] ) : '';
		$title   = isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '';
		$message = isset( $data['message'] ) ? (string) $data['message'] : '';

		if ( $source === '' || $type === '' || $title === '' || $message === '' ) {
			return 0;
		}

		$priority = isset( $data['priority'] ) ? (int) $data['priority'] : 50;
		$priority = max( 0, min( 100, (int) $priority ) );

		$tags = null;
		if ( isset( $data['tags'] ) ) {
			$tags = is_array( $data['tags'] ) ? wp_json_encode( $data['tags'] ) : (string) $data['tags'];
		}

		$context = null;
		if ( isset( $data['context'] ) ) {
			$context = is_array( $data['context'] ) ? wp_json_encode( $data['context'] ) : (string) $data['context'];
		}

		$status = isset( $data['status'] ) ? (int) $data['status'] : 0;

		$wpdb->insert(
			$this->table,
			array(
				'source'     => $source,
				'type'       => $type,
				'title'      => $title,
				'message'    => $message,
				'status'     => $status,
				'priority'   => $priority,
				'context'    => $context,
				'tags'       => $tags,
				'created_at' => $now,
				'updated_at' => null,
				'read_at'    => null,
			),
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public function update_status( int $id, int $status ) {
		global $wpdb;

		return $wpdb->update(
			$this->table,
			array(
				'status'     => (int) $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => (int) $id ),
			array( '%d', '%s' ),
			array( '%d' )
		);
	}

	public function get_list( array $filters = array(), int $page = 1, int $per_page = 20 ): array {
		global $wpdb;

		$wheres = array( '1=1' );
		$args   = array();

		if ( ! empty( $filters['source'] ) ) {
			$wheres[] = 'source = %s';
			$args[]   = sanitize_text_field( (string) $filters['source'] );
		}

		if ( isset( $filters['status'] ) ) {
			$wheres[] = 'status = %d';
			$args[]   = (int) $filters['status'];
		}

		$where_sql = implode( ' AND ', $wheres );
		$offset    = max( 0, ( $page - 1 ) * $per_page );

		$args[] = (int) $per_page;
		$args[] = (int) $offset;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table}
			 WHERE {$where_sql}
			 ORDER BY status ASC, priority DESC, created_at DESC
			 LIMIT %d OFFSET %d",
			...$args
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public function delete_old( int $days = 90 ) {
		global $wpdb;

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table}
				 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				(int) $days
			)
		);
	}
}
