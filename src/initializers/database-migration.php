<?php
/**
 * Database Migration
 *
 * Schema creation and upgrades.
 * (Extracted from NH_Database::maybe_upgrade_database)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database_Migration {

	public const DB_VERSION = '2.0.0';

	public static function run() {
		$installed = (string) get_option( 'nh_db_version', '' );

		if ( $installed === self::DB_VERSION ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		self::create_notifications_table();
		self::create_hooks_table();
		self::add_missing_columns();
		self::add_indexes();

		update_option( 'nh_db_version', self::DB_VERSION );
	}

	private static function create_notifications_table() {
		global $wpdb;

		$charset = $wpdb->get_charset_collate();
		$table   = $wpdb->prefix . 'nh_notifications';

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

	private static function create_hooks_table() {
		global $wpdb;

		$charset = $wpdb->get_charset_collate();
		$table   = $wpdb->prefix . 'nh_hooks';

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

	private static function add_missing_columns() {
		global $wpdb;

		$table = $wpdb->prefix . 'nh_notifications';

		self::ensure_column( $table, 'read_at', "ALTER TABLE {$table} ADD COLUMN read_at DATETIME NULL AFTER updated_at" );
		self::ensure_column( $table, 'priority', "ALTER TABLE {$table} ADD COLUMN priority TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER status" );
		self::ensure_column( $table, 'tags', "ALTER TABLE {$table} ADD COLUMN tags LONGTEXT NULL AFTER context" );
	}

	private static function add_indexes() {
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
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE {$table} ADD KEY idx_status_priority_created (status, priority, created_at)" );
		}
	}

	private static function ensure_column( string $table, string $column, string $alter_sql ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$col = $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE '{$column}'" );

		if ( empty( $col ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $alter_sql );
		}
	}
}
