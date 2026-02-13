<?php
/**
 * Custom Hooks Repository
 *
 * CRUD for custom hooks table.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Custom_Hooks {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'nh_hooks';
	}

	public function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY id DESC", ARRAY_A );
	}

	public function get( int $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ), ARRAY_A );
	}

	public function insert( array $data ): int {
		global $wpdb;

		$wpdb->insert(
			$this->table,
			array(
				'title'       => sanitize_text_field( $data['title'] ?? '' ),
				'action_name' => sanitize_text_field( $data['action_name'] ?? '' ),
				'channels'    => is_array( $data['channels'] ) ? wp_json_encode( $data['channels'] ) : null,
				'status'      => isset( $data['status'] ) ? (int) $data['status'] : 1,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public function update( int $id, array $data ) {
		global $wpdb;

		return $wpdb->update(
			$this->table,
			array(
				'title'       => sanitize_text_field( $data['title'] ?? '' ),
				'action_name' => sanitize_text_field( $data['action_name'] ?? '' ),
				'channels'    => is_array( $data['channels'] ) ? wp_json_encode( $data['channels'] ) : null,
				'status'      => isset( $data['status'] ) ? (int) $data['status'] : 1,
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);
	}

	public function delete( int $id ) {
		global $wpdb;
		return $wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
	}
}
