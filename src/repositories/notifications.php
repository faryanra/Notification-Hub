<?php
/**
 * Notifications Repository
 *
 * Database CRUD operations for notifications.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications Repository Class
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
	 * Insert a notification.
	 *
	 * @param array $data Notification data.
	 * @return int Inserted notification ID (0 on failure).
	 */
	public function insert( array $data ) {
		global $wpdb;

		$now = current_time( 'mysql' );

		$source  = isset( $data['source'] ) ? sanitize_text_field( (string) $data['source'] ) : '';
		$type    = isset( $data['type'] ) ? sanitize_text_field( (string) $data['type'] ) : '';
		$title   = isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '';
		$message = isset( $data['message'] ) ? (string) $data['message'] : '';

		if ( '' === $source || '' === $type || '' === $title || '' === $message ) {
			return 0;
		}

		$priority = array_key_exists( 'priority', $data ) ? (int) $data['priority'] : null;
		if ( null === $priority ) {
			$priority = $this->infer_priority( $source, $type );
		}
		$priority = max( 0, min( 100, (int) $priority ) );

		// Tags (store JSON array).
		$tags = null;
		if ( isset( $data['tags'] ) ) {
			$tags = is_array( $data['tags'] ) ? wp_json_encode( $data['tags'] ) : (string) $data['tags'];
		}
		if ( null === $tags ) {
			$fallback = array_values( array_unique( array_filter( array( $source, $type ) ) ) );
			$tags     = $fallback ? wp_json_encode( $fallback ) : null;
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

	/**
	 * Update notification status.
	 *
	 * @param int $id     Notification ID.
	 * @param int $status Status code.
	 * @return int|false Rows affected or false on error.
	 */
	public function update_status( $id, $status ) {
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

	/**
	 * Mark notification as read.
	 *
	 * @param int $id Notification ID.
	 * @return int|false Rows affected or false on error.
	 */
	public function mark_read( $id ) {
		global $wpdb;

		return $wpdb->update(
			$this->table,
			array(
				'read_at'    => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Mark notification as unread.
	 *
	 * @param int $id Notification ID.
	 * @return int|false Rows affected or false on error.
	 */
	public function mark_unread( $id ) {
		global $wpdb;

		return $wpdb->update(
			$this->table,
			array(
				'read_at'    => null,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => (int) $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a notification.
	 *
	 * @param int $id Notification ID.
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
	 * Get notifications list (paged).
	 *
	 * @param array $filters  Filters.
	 * @param int   $page     Page number.
	 * @param int   $per_page Items per page.
	 * @return array
	 */
	public function get_list( array $filters = array(), $page = 1, $per_page = 20 ) {
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

		if ( isset( $filters['min_priority'] ) ) {
			$wheres[] = 'priority >= %d';
			$args[]   = (int) $filters['min_priority'];
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

	/**
	 * Get single notification by ID.
	 *
	 * @param int $id Notification ID.
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
	 * Count notifications.
	 *
	 * @param array $filters Filters.
	 * @return int
	 */
	public function count( array $filters = array() ) {
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

		if ( empty( $args ) ) {
			$sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where_sql}";
			return (int) $wpdb->get_var( $sql );
		}

		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table} WHERE {$where_sql}",
			...$args
		);

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Cleanup old notifications.
	 *
	 * @param int $days Retention days.
	 * @return int|false Rows affected or false on error.
	 */
	public function cleanup_old( $days = 90 ) {
		global $wpdb;

		// Multisite: enforce network retention (Pro policy).
		if ( function_exists( 'get_site_option' ) && is_multisite() ) {
			$policy = get_site_option( 'nh_network_policy', array() );
			if ( ! empty( $policy['retention_days'] ) ) {
				$days = (int) $policy['retention_days'];
			}
		}

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table}
				 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				(int) $days
			)
		);
	}

	/**
	 * Infer priority when not provided.
	 *
	 * @param string $source Source slug.
	 * @param string $type   Type slug.
	 * @return int Priority (0-100).
	 */
	private function infer_priority( $source, $type ) {
		$src = strtolower( $source );
		$typ = strtolower( $type );

		$has = function ( $haystack, $needle ) {
			return '' !== $needle && false !== strpos( $haystack, $needle );
		};

		if ( $has( $src, 'woocommerce' ) || $has( $typ, 'order' ) ) {
			return 80;
		}
		if ( $has( $typ, 'comment' ) ) {
			return 60;
		}

		if ( $has( $src, 'cf7' ) || $has( $typ, 'form' ) || $has( $typ, 'cf7' ) ) {
			return 55;
		}

		if (
			$has( $src, 'security' ) ||
			$has( $src, 'wordfence' ) ||
			$has( $typ, 'security' ) ||
			$has( $typ, 'error' )
		) {
			return 90;
		}

		return 50;
	}
}
