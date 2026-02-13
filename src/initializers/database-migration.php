<?php
/**
 * Database Migration Initializer
 *
 * Handles database schema creation and migrations.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database_Migration Class
 */
class Database_Migration {

	/**
	 * Database schema version.
	 *
	 * @var string
	 */
	const DB_VERSION = '2.0.0';

	/**
	 * Run database migrations.
	 *
	 * @return void
	 */
	public static function run() {
		$installed = (string) get_option( 'nh_db_version', '' );

		if ( $installed === self::DB_VERSION ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Create/update notifications table.
		self::create_notifications_table();

		// Create/update hooks table.
		self::create_hooks_table();

		// Ensure additional columns exist (backward compatibility).
		self::ensure_notifications_columns();

		// Ensure indexes exist.
		self::ensure_notifications_indexes();

		update_option( 'nh_db_version', self::DB_VERSION );
	}

	/**
	 * Create notifications table.
	 *
	 * @return void
	 */
	private static function create_notifications_table() {
		global $wpdb;

		$table   = $wpdb->prefix . 'nh_notifications';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source VARCHAR(64) NOT NULL,
			type VARCHAR(64) NOT NULL,
			title VARCHAR(255) NOT NULL,
			message LONGTEXT NOT NULL,
			status TINYINT NOT NULL DEFAULT 0,
			priority TINYINT UNSIGNED NOT NULL DEFAULT 50,
			context LONGTEXT NULL,
			tags LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			read_at DATETIME NULL,
			PRIMARY KEY (id),
			KEY idx_source (source),
			KEY idx_status_created (status, created_at),
			KEY idx_type_created (type, created_at),
			KEY idx_status_priority_created (status, priority, created_at)
		) {$charset};";

		dbDelta( $sql );
	}

	/**
	 * Create hooks table.
	 *
	 * @return void
	 */
	private static function create_hooks_table() {
		global $wpdb;

		$table   = $wpdb->prefix . 'nh_hooks';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(160) NOT NULL,
			action_name VARCHAR(160) NOT NULL,
			channels LONGTEXT NULL,
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY action_name (action_name)
		) {$charset};";

		dbDelta( $sql );
	}

	/**
	 * Ensure additional columns exist (backward compatibility).
	 *
	 * @return void
	 */
	private static function ensure_notifications_columns() {
		global $wpdb;

		$table = $wpdb->prefix . 'nh_notifications';

		self::ensure_column_exists(
			$table,
			'read_at',
			"ALTER TABLE {$table} ADD COLUMN read_at DATETIME NULL AFTER updated_at"
		);

		self::ensure_column_exists(
			$table,
			'priority',
			"ALTER TABLE {$table} ADD COLUMN priority TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER status"
		);

		self::ensure_column_exists(
			$table,
			'tags',
			"ALTER TABLE {$table} ADD COLUMN tags LONGTEXT NULL AFTER context"
		);
	}

	/**
	 * Ensure indexes exist.
	 *
	 * @return void
	 */
	private static function ensure_notifications_indexes() {
		global $wpdb;

		$table = $wpdb->prefix . 'nh_notifications';

		$idx = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT INDEX_NAME
				 FROM INFORMATION_SCHEMA.STATISTICS
				 WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND INDEX_NAME='idx_status_priority_created'",
				DB_NAME,
				str_replace( $wpdb->prefix, '', $table )
			)
		);

		if ( ! $idx ) {
			$wpdb->query( "ALTER TABLE {$table} ADD KEY idx_status_priority_created (status, priority, created_at)" );
		}
	}

	/**
	 * Ensure a column exists; otherwise run an ALTER statement.
	 *
	 * @param string $table     Table name.
	 * @param string $column    Column name.
	 * @param string $alter_sql ALTER TABLE statement.
	 * @return void
	 */
	private static function ensure_column_exists( $table, $column, $alter_sql ) {
		global $wpdb;

		$col = $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE '{$column}'" );

		if ( empty( $col ) ) {
			$wpdb->query( $alter_sql );
		}
	}
}
