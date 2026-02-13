<?php
/**
 * Queue Migration
 *
 * Queue table schema.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Initializers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Queue_Migration {

	public static function run() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$table   = $wpdb->prefix . 'nh_queue';

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			notification_id BIGINT UNSIGNED NOT NULL,
			channel VARCHAR(64) NOT NULL,
			payload LONGTEXT NULL,
			status VARCHAR(32) NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL,
			processed_at DATETIME NULL,
			PRIMARY KEY (id),
			KEY status (status)
		) {$charset};";

		dbDelta( $sql );
	}
}
