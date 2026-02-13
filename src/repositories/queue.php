<?php
/**
 * Queue Repository
 *
 * CRUD for queue table.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Queue {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'nh_queue';
	}

	public function push( array $item ): int {
		global $wpdb;

		$wpdb->insert(
			$this->table,
			array(
				'notification_id' => (int) $item['notification_id'],
				'channel'         => sanitize_text_field( $item['channel'] ?? '' ),
				'payload'         => wp_json_encode( $item['payload'] ?? array() ),
				'status'          => 'pending',
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public function get_pending( int $limit = 10 ): array {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE status = 'pending' ORDER BY id ASC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}

	public function mark_processed( int $id ) {
		global $wpdb;

		return $wpdb->update(
			$this->table,
			array(
				'status'       => 'processed',
				'processed_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}
}
