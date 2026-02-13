<?php
/**
 * Custom Hooks Repository
 *
 * Database CRUD for custom hooks table.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Hooks Repository
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
		$this->table = $wpdb->prefix . 'nh_custom_hooks';
	}

	/**
	 * Create custom hook.
	 *
	 * @param array $data Hook data.
	 * @return int|false Hook ID or false.
	 */
	public function create( array $data ) {
		global $wpdb;

		$defaults = array(
			'hook_name' => '',
			'title'     => '',
			'message'   => '',
			'status'    => 'active',
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
	 * Get hook by ID.
	 *
	 * @param int $id Hook ID.
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
	 * Get all hooks.
	 *
	 * @return array
	 */
	public function get_all() {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT * FROM {$this->table} ORDER BY created_at DESC"
		);
	}

	/**
	 * Update hook.
	 *
	 * @param int   $id   Hook ID.
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
	 * Delete hook.
	 *
	 * @param int $id Hook ID.
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
}
