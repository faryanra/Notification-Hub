<?php
// Database schema, CRUD, and migration

if (!defined('ABSPATH')) exit;

class NH_Database {
    const DB_VERSION = '1.5.0';
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nh_notifications';
    }

    public function maybe_upgrade_database() {
        $installed = get_option('nh_db_version');
        if ($installed !== self::DB_VERSION) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($this->schema_notifications());
            $this->create_hooks_table(); 

            global $wpdb;
            $table = $wpdb->prefix . 'nh_notifications';
            $col = $wpdb->get_results($wpdb->prepare(
                "SHOW COLUMNS FROM {$table} LIKE %s", 'read_at'
            ));
            if (!$col) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN read_at DATETIME NULL AFTER updated_at");
            }

            update_option('nh_db_version', self::DB_VERSION);
        }
    }

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
            read_at DATETIME NULL,
            PRIMARY KEY  (id),
            KEY idx_source (source),
            KEY idx_status_created (status, created_at),
            KEY idx_type_created (type, created_at)
        ) $charset;";
    }

    private function create_hooks_table() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'nh_hooks';
        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(160) NOT NULL,
            action_name VARCHAR(160) NOT NULL,
            channels LONGTEXT NULL, 
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action_name (action_name)
        ) $charset;";
        dbDelta($sql);
    }

    public function insert_notification(array $e) {
        global $wpdb;
        $now = current_time('mysql');
        $result = $wpdb->insert($this->table, [
            'source'     => $e['source'],
            'type'       => $e['type'],
            'title'      => $e['title'],
            'message'    => $e['message'],
            'status'     => isset($e['status']) ? (int)$e['status'] : 0,
            'context'    => isset($e['context']) ? wp_json_encode($e['context']) : null,
            'created_at' => $now,
            'updated_at' => null,
            'read_at'    => null,
        ], ['%s','%s','%s','%s','%d','%s','%s','%s','%s']);

        if ($wpdb->last_error) {
            error_log("❌ DB Insert Error: " . $wpdb->last_error);
        }

        return (int) $wpdb->insert_id;
    }

    public function mark_status($id, $status) {
        global $wpdb;
        $res = $wpdb->update($this->table, [
            'status'     => (int)$status,
            'updated_at' => current_time('mysql'),
        ], ['id' => (int)$id], ['%d','%s'], ['%d']);

        if ($wpdb->last_error) {
            error_log("❌ DB Update Error: " . $wpdb->last_error);
        }

        return $res;
    }

    public function get_list(array $filters=[], $page=1, $per_page=20) {
        global $wpdb;
        $wheres = ['1=1'];
        $args = [];

        if (!empty($filters['source'])) {
            $wheres[] = 'source = %s';
            $args[] = $filters['source'];
        }

        if (isset($filters['status'])) {
            $wheres[] = 'status = %d';
            $args[] = (int)$filters['status'];
        }

        $where = implode(' AND ', $wheres);
        $offset = max(0, ($page - 1) * $per_page);

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            array_merge($args, [$per_page, $offset])
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function cleanup_old($days = 90) {
        global $wpdb;
        $res = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            (int) $days
        ));

        if ($wpdb->last_error) {
            error_log("❌ DB Cleanup Error: " . $wpdb->last_error);
        }

        return $res;
    }
}
