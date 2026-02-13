<?php
/**
 * Queue Repository
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queue Repository
 */
class Queue {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'nh_queue';
	}

	public function add( array $data ) {
		global $wpdb;

		$defaults = array(
			'notification_id' => 0,
			'channel'         => 'email',
			'status'          => 'pending',
			'created_at'      => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		$result = $wpdb->insert( $this->table, $data );

		return $result ? $wpdb->insert_id : false;
	}

	public function get_pending( $limit = 10 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE status = 'pending' ORDER BY created_at ASC LIMIT %d",
				$limit
			)
		);
	}

	public function update_status( $id, $status ) {
		global $wpdb;

		return $wpdb->update(
			$this->table,
			array( 'status' => $status ),
			array( 'id' => $id )
		);
	}
}
