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
            payload LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            attempts INT NOT NULL DEFAULT 0,
            available_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
