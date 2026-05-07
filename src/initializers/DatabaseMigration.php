<?php

namespace NotificationHub\Initializers;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database migrations (schema install/upgrade).
 *
 * Ported from legacy NH_Database::maybe_upgrade_database().
 *
 * @since 1.0.0
 */
final class DatabaseMigration implements Integration {
    /**
     * DB schema version.
     *
     * Bump this when schema changes.
     *
     * @since 1.0.0
     */
    public const DB_VERSION = '1.0.0';

    public function register(Loader $loader): void {
        // Ensure schema is ready for both admin and frontend flows.
        $loader->addAction('plugins_loaded', [$this, 'maybeUpgrade'], 7);
    }

    public function maybeUpgrade(): void {
        $installed = (string) get_option('nh_db_version', '');
        if ($installed === self::DB_VERSION) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($this->schemaNotifications());
        $this->createHooksTable();
        $this->createLogsTable();
        $this->createRulesTable();

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $this->ensureColumnExists($table, 'read_at', "ALTER TABLE {$table} ADD COLUMN read_at DATETIME NULL AFTER updated_at");
        $this->ensureColumnExists($table, 'priority', "ALTER TABLE {$table} ADD COLUMN priority TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER status");
        $this->ensureColumnExists($table, 'tags', "ALTER TABLE {$table} ADD COLUMN tags LONGTEXT NULL AFTER context");
        $this->ensureIndexExists($table, 'idx_created_at', "ALTER TABLE {$table} ADD INDEX idx_created_at (created_at)");

        // idx_status_priority_created is already included in schemaNotifications(); dbDelta will create it.
        // Do NOT run a manual ALTER here; it can cause duplicate-key warnings/errors depending on DB state.

        update_option('nh_db_version', self::DB_VERSION);
    }

    private function schemaNotifications(): string {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $charset = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table} (
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
            KEY idx_created_at (created_at),
            KEY idx_status_created (status, created_at),
            KEY idx_type_created (type, created_at),
            KEY idx_status_priority_created (status, priority, created_at)
        ) {$charset};";
    }

    private function createHooksTable(): void {
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

        dbDelta($sql);
    }

    private function createLogsTable(): void {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $table   = $wpdb->prefix . 'nh_logs';

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            level VARCHAR(10) NOT NULL,
            scope VARCHAR(32) NOT NULL,
            event VARCHAR(64) NOT NULL,
            message VARCHAR(500) NOT NULL,
            context LONGTEXT NULL,
            PRIMARY KEY (id),
            KEY idx_scope_event (scope, event),
            KEY idx_level_created (level, created_at)
        ) {$charset};";

        dbDelta($sql);
    }

    private function createRulesTable(): void {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $table   = $wpdb->prefix . 'nh_rules';

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(190) NOT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            priority INT NOT NULL DEFAULT 100,
            conditions LONGTEXT NULL,
            actions LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_enabled_priority (enabled, priority),
            KEY idx_updated_at (updated_at)
        ) {$charset};";

        dbDelta($sql);
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

    private function ensureIndexExists(string $table, string $indexName, string $alterSql): void {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $idx = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM {$table} WHERE Key_name = %s", $indexName));

        if (empty($idx)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query($alterSql);
        }
    }
}
