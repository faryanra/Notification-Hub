<?php
// NH v1.3.0 — Database schema, CRUD, and Hook migration

if (!defined('ABSPATH')) exit;

class NH_Database {
    const DB_VERSION = '1.3.0';
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nh_notifications';
    }

    /**
     * NH v1.2.0 — Ensure notifications table exists/updated
     * NH v1.3.0 — Add nh_hooks table for custom hook management
     */
    public function maybe_upgrade_database() {
        $installed = get_option('nh_db_version');
        if ($installed !== self::DB_VERSION) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($this->schema_notifications());
            $this->create_hooks_table(); // ← New in v1.3.0
            update_option('nh_db_version', self::DB_VERSION);
        }
    }

    /**
     * NH v1.2.0 — Notifications table
     */
    private function schema_notifications() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        return "CREATE TABLE {$this->table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source VARCHAR(64) NOT NULL,
            type VARCHAR(64) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message LONGTEXT NOT NULL,
            status TINYINT NOT NULL DEFAULT 0,
            context LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY  (id),
            KEY idx_source (source),
            KEY idx_status_created (status, created_at),
            KEY idx_type_created (type, created_at)
        ) $charset;";
    }

    /**
     * NH v1.3.0 — Create nh_hooks table for custom hook definitions
     */
    private function create_hooks_table() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        $hooks_table = $wpdb->prefix . 'nh_hooks';
        $sql_hooks = "CREATE TABLE $hooks_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(160) NOT NULL,
            action_name VARCHAR(160) NOT NULL,
            channels TEXT NULL, -- JSON: ['email','telegram',...]
            status TINYINT(1) NOT NULL DEFAULT 1, -- 1 active, 0 inactive
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action_name (action_name)
        ) $charset_collate;";

        dbDelta($sql_hooks);
    }

    /**
     * NH v1.2.0 — Insert new notification record
     */
    public function insert_notification(array $e) {
        global $wpdb;
        $now = current_time('mysql');
        $wpdb->insert($this->table, [
            'source'     => $e['source'],
            'type'       => $e['type'],
            'title'      => $e['title'],
            'message'    => $e['message'],
            'status'     => isset($e['status']) ? (int)$e['status'] : 0,
            'context'    => isset($e['context']) ? wp_json_encode($e['context']) : null,
            'created_at' => $now,
            'updated_at' => null,
        ], ['%s','%s','%s','%s','%d','%s','%s','%s']);
        return (int) $wpdb->insert_id;
    }

    /**
     * NH v1.2.0 — Fetch notifications with pagination
     */
    public function get_list(array $filters=[], $page=1, $per_page=20) {
        global $wpdb;
        $wheres = ['1=1'];
        $args = [];
        if (!empty($filters['source'])) { $wheres[]='source=%s'; $args[]=$filters['source']; }
        if (isset($filters['status'])) { $wheres[]='status=%d'; $args[]=(int)$filters['status']; }
        $where = implode(' AND ', $wheres);
        $offset = max(0, ($page-1)*$per_page);
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            array_merge($args, [$per_page, $offset])
        );
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * NH v1.2.0 — Update notification status
     */
    public function mark_status($id, $status) {
        global $wpdb;
        return $wpdb->update($this->table, [
            'status' => (int)$status,
            'updated_at' => current_time('mysql'),
        ], ['id'=>(int)$id], ['%d','%s'], ['%d']);
    }

    /**
     * NH v1.2.0 — Cleanup old records (retention)
     */
    public function cleanup_old($days=90) {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            (int)$days
        ));
    }
}
