<?php
// Prevent direct access to this file for security reasons
if ( ! defined( 'ABSPATH' ) ) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

/**
 * Class NH_Database
 * This class handles database operations, including creating the custom table for notifications.
 * It ensures the table is created only on plugin activation for efficiency.
 */
class NH_Database {

    /**
     * Creates or updates the notifications table in the database.
     * Why? To store notifications persistently with a custom structure for better performance than using options or post meta.
     * This method is hooked to plugin activation.
     */
    public static function create_table() {
        global $wpdb;  // WordPress database object for queries

        $table_name = $wpdb->prefix . 'nh_notifications';  // Prefix table name for multisite compatibility (e.g., wp_nh_notifications)
        $charset_collate = $wpdb->get_charset_collate();  // Get database charset and collate for proper encoding (supports languages like Persian)

        // SQL query to create the table with necessary columns (no comments inside SQL to avoid syntax errors)
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source VARCHAR(50) NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'info',
            title TEXT NOT NULL,
            message LONGTEXT NOT NULL,
            status ENUM('new', 'read', 'archived') NOT NULL DEFAULT 'new',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            meta JSON DEFAULT NULL
        ) $charset_collate;";

        // Include WordPress upgrade file to use dbDelta
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );  // Loads dbDelta function to create/update table safely

        dbDelta( $sql );  // Executes the query – creates table if not exists, or updates schema if changed
    }
}