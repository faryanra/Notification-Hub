<?php
/**
 * Database Migration
 *
 * Creates and updates database tables.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Migration
 */
class Database_Migration {

	/**
	 * Current schema version.
	 *
	 * @var string
	 */
	const SCHEMA_VERSION = '2.0.0';

	/**
	 * Run migrations.
	 *
	 * @return void
	 */
	public static function run() {
		$installed_version = get_option( 'nh_schema_version', '0' );

		if ( version_compare( $installed_version, self::SCHEMA_VERSION, '>=' ) ) {
			return;
		}

		self::create_notifications_table();
		self::create_custom_hooks_table();

		update_option( 'nh_schema_version', self::SCHEMA_VERSION );
	}

	/**
	 * Create notifications table.
	 *
	 * @return void
	 */
	private static function create_notifications_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'nh_notifications';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			message text NOT NULL,
			type varchar(50) NOT NULL DEFAULT 'general',
			status varchar(20) NOT NULL DEFAULT 'unread',
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY status (status),
			KEY type (type),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create custom hooks table.
	 *
	 * @return void
	 */
	private static function create_custom_hooks_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'nh_custom_hooks';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			hook_name varchar(100) NOT NULL,
			title varchar(255) NOT NULL,
			message text NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY hook_name (hook_name),
			KEY status (status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
