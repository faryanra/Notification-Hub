<?php

namespace NotificationHub\Initializers;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Ensures queue table exists / migrated.
 *
 * @since 1.7.2
 */
final class QueueMigration implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('plugins_loaded', [$this, 'maybeMigrate'], 6);
    }

    public function maybeMigrate(): void {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_queue';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            channel VARCHAR(20) NOT NULL DEFAULT 'email',
            payload LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            attempts INT NOT NULL DEFAULT 0,
            retry_limit INT NOT NULL DEFAULT 3,
            available_at DATETIME NULL,
            last_error LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY status_available (status, available_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $this->ensureColumnExists($table, 'channel', "ALTER TABLE {$table} ADD COLUMN channel VARCHAR(20) NOT NULL DEFAULT 'email'");
        $this->ensureColumnExists($table, 'payload', "ALTER TABLE {$table} ADD COLUMN payload LONGTEXT NOT NULL");
        $this->ensureColumnExists($table, 'status', "ALTER TABLE {$table} ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        $this->ensureColumnExists($table, 'attempts', "ALTER TABLE {$table} ADD COLUMN attempts INT NOT NULL DEFAULT 0");
        $this->ensureColumnExists($table, 'retry_limit', "ALTER TABLE {$table} ADD COLUMN retry_limit INT NOT NULL DEFAULT 3");
        $this->ensureColumnExists($table, 'available_at', "ALTER TABLE {$table} ADD COLUMN available_at DATETIME NULL");
        $this->ensureColumnExists($table, 'last_error', "ALTER TABLE {$table} ADD COLUMN last_error LONGTEXT NULL");
        $this->ensureColumnExists($table, 'created_at', "ALTER TABLE {$table} ADD COLUMN created_at DATETIME NOT NULL");
        $this->ensureColumnExists($table, 'updated_at', "ALTER TABLE {$table} ADD COLUMN updated_at DATETIME NULL");
    }

    private function ensureColumnExists(string $table, string $column, string $alterSql): void {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $col = $wpdb->get_results("SHOW COLUMNS FROM {$table} LIKE '{$column}'");

        if (empty($col)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query($alterSql);
        }
    }
}
