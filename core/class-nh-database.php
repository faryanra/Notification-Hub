<?php
// NH v1.2.0 — Database schema & CRUD

if (!defined('ABSPATH')) exit;

class NH_Database {
    const DB_VERSION = '1.0';
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nh_notifications';
    }

    public function maybe_upgrade_database() {
        // NH v1.2.0 — Ensure notifications table exists/updated
        $installed = get_option('nh_db_version');
        if ($installed !== self::DB_VERSION) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($this->schema_notifications());
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
            PRIMARY KEY  (id),
            KEY idx_source (source),
            KEY idx_status_created (status, created_at),
            KEY idx_type_created (type, created_at)
        ) $charset;";
    }

    public function insert_notification(array $e) {
        // NH v1.2.0 — Insert event (expects sanitized payload)
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

    public function get_list(array $filters=[], $page=1, $per_page=20) {
        // NH v1.2.0 — Paginated fetch with basic filters
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

    public function mark_status($id, $status) {
        // NH v1.2.0 — Update status
        global $wpdb;
        return $wpdb->update($this->table, [
            'status' => (int)$status,
            'updated_at' => current_time('mysql'),
        ], ['id'=>(int)$id], ['%d','%s'], ['%d']);
    }

    public function cleanup_old($days=90) {
        // NH v1.2.0 — Delete old notifications (retention policy)
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            (int)$days
        ));
    }
}
