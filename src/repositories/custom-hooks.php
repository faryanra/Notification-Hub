<?php
/**
 * Custom Hooks Repository
 *
 * Database CRUD operations for custom hooks.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom_Hooks Repository Class
 */
class Custom_Hooks {

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
		$this->table = $wpdb->prefix . 'nh_hooks';
	}

	/**
	 * Insert a new hook.
	 *
	 * @param array $data Hook data.
	 * @return int Inserted hook ID (0 on failure).
	 */
	public function insert( array $data ) {
		global $wpdb;

		$title   = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$action  = isset( $data['action_name'] ) ? sanitize_text_field( $data['action_name'] ) : '';
		$channels = isset( $data['channels'] ) ? $data['channels'] : array();
		$status  = isset( $data['status'] ) ? (int) $data['status'] : 1;

		if ( '' === $title || '' === $action ) {
			return 0;
		}

		// Check duplicate.
		if ( $this->exists_by_action( $action ) ) {
			return 0;
		}

		$channels_json = is_array( $channels ) ? wp_json_encode( array_values( array_unique( $channels ) ) ) : null;

		$wpdb->insert(
			$this->table,
			array(
				'title'       => $title,
				'action_name' => $action,
				'channels'    => $channels_json,
				'status'      => $status,
			),
			array( '%s', '%s', '%s', '%d' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update a hook.
	 *
	 * @param int   $id   Hook ID.
	 * @param array $data Hook data.
	 * @return int|false Rows affected or false on error.
	 */
	public function update( $id, array $data ) {
		global $wpdb;

		$title    = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		$action   = isset( $data['action_name'] ) ? sanitize_text_field( $data['action_name'] ) : '';
		$channels = isset( $data['channels'] ) ? $data['channels'] : array();

		if ( '' === $title || '' === $action ) {
			return false;
		}

		// Check duplicate (exclude current ID).
		$existing = $this->get_by_action( $action );
		if ( $existing && (int) $existing['id'] !== (int) $id ) {
			return false;
		}

		$channels_json = is_array( $channels ) ? wp_json_encode( array_values( array_unique( $channels ) ) ) : null;

		return $wpdb->update(
			$this->table,
			array(
				'title'       => $title,
				'action_name' => $action,
				'channels'    => $channels_json,
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a hook.
	 *
	 * @param int $id Hook ID.
	 * @return int|false Rows affected or false on error.
	 */
	public function delete( $id ) {
		global $wpdb;

		return $wpdb->delete(
			$this->table,
			array( 'id' => (int) $id ),
			array( '%d' )
		);
	}

	/**
	 * Get all hooks.
	 *
	 * @return array
	 */
	public function get_all() {
		global $wpdb;

		$sql = "SELECT * FROM {$this->table} ORDER BY id DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get active hooks only.
	 *
	 * @return array
	 */
	public function get_active() {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE status = %d ORDER BY id DESC",
			1
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get hook by ID.
	 *
	 * @param int $id Hook ID.
	 * @return array|null
	 */
	public function get_by_id( $id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
			(int) $id
		);

		$result = $wpdb->get_row( $sql, ARRAY_A );

		return $result ? $result : null;
	}

	/**
	 * Get hook by action name.
	 *
	 * @param string $action_name Action name.
	 * @return array|null
	 */
	public function get_by_action( $action_name ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE action_name = %s LIMIT 1",
			sanitize_text_field( $action_name )
		);

		$result = $wpdb->get_row( $sql, ARRAY_A );

		return $result ? $result : null;
	}

	/**
	 * Check if hook exists by action name.
	 *
	 * @param string $action_name Action name.
	 * @return bool
	 */
	public function exists_by_action( $action_name ) {
		global $wpdb;

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE action_name = %s",
				sanitize_text_field( $action_name )
			)
		);

		return $count > 0;
	}
}
