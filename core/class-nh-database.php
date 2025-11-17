<?php
// Database schema, CRUD, and migration

if (!defined('ABSPATH')) exit;

class NH_Database {
    const DB_VERSION = '1.6.0'; 
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

            // --- Ensure 'read_at' exists (legacy support) ---
            $col = $wpdb->get_results("SHOW COLUMNS FROM {$table} LIKE 'read_at'");
            if (!$col) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN read_at DATETIME NULL AFTER updated_at");
            }

            // --- Ensure 'priority' exists ---
            $col = $wpdb->get_results("SHOW COLUMNS FROM {$table} LIKE 'priority'");
            if (!$col) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN priority TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER status");
            }

            // --- Ensure 'tags' exists ---
            $col = $wpdb->get_results("SHOW COLUMNS FROM {$table} LIKE 'tags'");
            if (!$col) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN tags LONGTEXT NULL AFTER context");
            }

            // --- Ensure compound index exists ---
            $idx = $wpdb->get_var($wpdb->prepare(
                "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND INDEX_NAME='idx_status_priority_created'",
                DB_NAME,
                str_replace($wpdb->prefix, '', $table)
            ));
            if (!$idx) {
                $wpdb->query("ALTER TABLE {$table} ADD KEY idx_status_priority_created (status, priority, created_at)");
            }

            // Update DB version marker
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
            priority TINYINT UNSIGNED NOT NULL DEFAULT 50,
            context LONGTEXT NULL,
            tags LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            read_at DATETIME NULL,
            PRIMARY KEY  (id),
            KEY idx_source (source),
            KEY idx_status_created (status, created_at),
            KEY idx_type_created (type, created_at),
            KEY idx_status_priority_created (status, priority, created_at)
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

        // Normalize optional fields
        $priority = isset($e['priority']) ? (int)$e['priority'] : null;

        // [NH v1.6.0] Fallback priority if not provided
        if ($priority === null) {
            $src = strtolower(isset($e['source']) ? (string)$e['source'] : '');
            $typ = strtolower(isset($e['type'])   ? (string)$e['type']   : '');

            if (str_contains($src, 'woocommerce') || str_contains($typ, 'order')) {
                $priority = 80;
            }
            elseif (str_contains($typ, 'comment')) {
                $priority = 60;
            }
            elseif (
                str_contains($src, 'cf7') ||
                str_contains($typ, 'form') ||
                str_contains($typ, 'cf7')
            ) {
                $priority = 55;
            }
            elseif (
                str_contains($src, 'security') ||
                str_contains($src, 'wordfence') ||
                str_contains($typ, 'security') ||
                str_contains($typ, 'error')
            ) {
                $priority = 90;
            }
            else {
                $priority = 50;
            }
        }

        $priority = max(0, min(100, (int)$priority));

        // Tags
        $tags = null;
        if (isset($e['tags'])) {
            $tags = is_array($e['tags']) ? wp_json_encode($e['tags']) : (string)$e['tags'];
        }
        if ($tags === null) {
            $fallback = array_values(array_unique(array_filter([
                isset($e['source']) ? (string)$e['source'] : '',
                isset($e['type'])   ? (string)$e['type']   : '',
            ])));
            $tags = $fallback ? wp_json_encode($fallback) : null;
        }

        // INSERT
        $result = $wpdb->insert(
            $this->table,
            [
                'source'     => $e['source'],
                'type'       => $e['type'],
                'title'      => $e['title'],
                'message'    => $e['message'],
                'status'     => isset($e['status']) ? (int)$e['status'] : 0,
                'priority'   => $priority,
                'context'    => isset($e['context']) ? wp_json_encode($e['context']) : null,
                'tags'       => $tags,
                'created_at' => $now,
                'updated_at' => null,
                'read_at'    => null,
            ],
            ['%s','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s']
        );

        if ($wpdb->last_error) {
            error_log("❌ DB Insert Error: " . $wpdb->last_error);
        }

        return (int)$wpdb->insert_id;
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

        // optional : filter based on the least priority
        if (isset($filters['min_priority'])) {
            $wheres[] = 'priority >= %d';
            $args[] = (int)$filters['min_priority'];
        }

        $where = implode(' AND ', $wheres);
        $offset = max(0, ($page - 1) * $per_page);

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE $where ORDER BY status ASC, priority DESC, created_at DESC LIMIT %d OFFSET %d",
            array_merge($args, [$per_page, $offset])
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function cleanup_old($days = 90) {
        global $wpdb;
        // [NH v1.6.0] Enforce network retention (Pro)
        if (function_exists('get_site_option') && is_multisite()) {
            $policy = get_site_option('nh_network_policy', []);
            if (!empty($policy['retention_days'])) {
                $days = (int) $policy['retention_days'];
            }
        }
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
