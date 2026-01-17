<?php
/**
 * NH_Database
 *
 * Database schema, CRUD, and migrations for Notification Hub.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Database {

    /**
     * Database schema version.
     *
     * Bump this when schema changes so maybe_upgrade_database() runs.
     *
     * @since 1.6.2
     */
    public const DB_VERSION = '1.6.2';

    /**
     * Notifications table name.
     *
     * @since 1.6.2
     * @var string
     */
    private $table;

    /**
     * Constructor.
     *
     * @since 1.6.2
     */
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nh_notifications';
    }

    /**
     * Create/upgrade DB schema if needed.
     *
     * @since 1.6.2
     * @return void
     */
    public function maybe_upgrade_database(): void {
        $installed = (string) get_option('nh_db_version', '');

        if ($installed === self::DB_VERSION) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        /**
         * Main table.
         */
        dbDelta($this->schema_notifications());

        /**
         * Hooks table.
         */
        $this->create_hooks_table();

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        /**
         * Ensure legacy columns exist (safe back-compat).
         */
        $this->ensure_column_exists($table, 'read_at', "ALTER TABLE {$table} ADD COLUMN read_at DATETIME NULL AFTER updated_at");
        $this->ensure_column_exists($table, 'priority', "ALTER TABLE {$table} ADD COLUMN priority TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER status");
        $this->ensure_column_exists($table, 'tags', "ALTER TABLE {$table} ADD COLUMN tags LONGTEXT NULL AFTER context");

        /**
         * Ensure compound index exists.
         */
        $idx = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT INDEX_NAME
                 FROM INFORMATION_SCHEMA.STATISTICS
                 WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND INDEX_NAME='idx_status_priority_created'",
                DB_NAME,
                str_replace($wpdb->prefix, '', $table)
            )
        );

        if (!$idx) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query("ALTER TABLE {$table} ADD KEY idx_status_priority_created (status, priority, created_at)");
        }

        update_option('nh_db_version', self::DB_VERSION);
    }

    /**
     * Build notifications table schema.
     *
     * @since 1.6.2
     * @return string SQL.
     */
    private function schema_notifications(): string {
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
            PRIMARY KEY (id),
            KEY idx_source (source),
            KEY idx_status_created (status, created_at),
            KEY idx_type_created (type, created_at),
            KEY idx_status_priority_created (status, priority, created_at)
        ) {$charset};";
    }

    /**
     * Create hooks table used by custom hooks and REST test trigger.
     *
     * @since 1.6.2
     * @return void
     */
    private function create_hooks_table(): void {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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

    /**
     * Ensure a column exists; otherwise run an ALTER statement.
     *
     * @since 1.6.2
     * @param string $table     Table name.
     * @param string $column    Column name.
     * @param string $alter_sql ALTER TABLE statement.
     * @return void
     */
    private function ensure_column_exists(string $table, string $column, string $alter_sql): void {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $col = $wpdb->get_results("SHOW COLUMNS FROM {$table} LIKE '{$column}'");

        if (empty($col)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query($alter_sql);
        }
    }

    /**
     * Insert a notification.
     *
     * @since 1.6.2
     * @param array $e Notification data.
     * @return int Inserted notification ID (0 on failure).
     */
    public function insert_notification(array $e): int {
        global $wpdb;

        $now = current_time('mysql');

        $source  = isset($e['source']) ? sanitize_text_field((string) $e['source']) : '';
        $type    = isset($e['type']) ? sanitize_text_field((string) $e['type']) : '';
        $title   = isset($e['title']) ? sanitize_text_field((string) $e['title']) : '';
        $message = isset($e['message']) ? (string) $e['message'] : '';

        if ($source === '' || $type === '' || $title === '' || $message === '') {
            return 0;
        }

        $priority = array_key_exists('priority', $e) ? (int) $e['priority'] : null;
        if ($priority === null) {
            $priority = $this->infer_priority($source, $type);
        }
        $priority = max(0, min(100, (int) $priority));

        /**
         * Tags (store JSON array if possible).
         */
        $tags = null;
        if (isset($e['tags'])) {
            $tags = is_array($e['tags']) ? wp_json_encode($e['tags']) : (string) $e['tags'];
        }
        if ($tags === null) {
            $fallback = array_values(array_unique(array_filter([$source, $type])));
            $tags = $fallback ? wp_json_encode($fallback) : null;
        }

        $context = null;
        if (isset($e['context'])) {
            $context = is_array($e['context']) ? wp_json_encode($e['context']) : (string) $e['context'];
        }

        $status = isset($e['status']) ? (int) $e['status'] : 0;

        $wpdb->insert(
            $this->table,
            [
                'source'     => $source,
                'type'       => $type,
                'title'      => $title,
                'message'    => $message,
                'status'     => $status,
                'priority'   => $priority,
                'context'    => $context,
                'tags'       => $tags,
                'created_at' => $now,
                'updated_at' => null,
                'read_at'    => null,
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * Infer priority when not provided.
     *
     * @since 1.6.2
     * @param string $source Source slug.
     * @param string $type   Type slug.
     * @return int Priority (0-100).
     */
    private function infer_priority(string $source, string $type): int {
        $src = strtolower($source);
        $typ = strtolower($type);

        // Use strpos for broader PHP compatibility (instead of str_contains).
        $has = static function (string $haystack, string $needle): bool {
            return $needle !== '' && strpos($haystack, $needle) !== false;
        };

        if ($has($src, 'woocommerce') || $has($typ, 'order')) {
            return 80;
        }
        if ($has($typ, 'comment')) {
            return 60;
        }

        if ($has($src, 'cf7') || $has($typ, 'form') || $has($typ, 'cf7')) {
            return 55;
        }

        if (
            $has($src, 'security') ||
            $has($src, 'wordfence') ||
            $has($typ, 'security') ||
            $has($typ, 'error')
        ) {
            return 90;
        }

        return 50;
    }

    /**
     * Update notification status.
     *
     * @since 1.6.2
     * @param int $id     Notification ID.
     * @param int $status Status code.
     * @return int|false Rows affected or false on error.
     */
    public function mark_status(int $id, int $status) {
        global $wpdb;

        return $wpdb->update(
            $this->table,
            [
                'status'     => (int) $status,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => (int) $id],
            ['%d', '%s'],
            ['%d']
        );
    }

    /**
     * Get notifications list (paged).
     *
     * @since 1.6.2
     * @param array $filters  Filters.
     * @param int   $page     Page number.
     * @param int   $per_page Items per page.
     * @return array<int, array<string, mixed>>
     */
    public function get_list(array $filters = [], int $page = 1, int $per_page = 20): array {
        global $wpdb;

        $wheres = ['1=1'];
        $args   = [];

        if (!empty($filters['source'])) {
            $wheres[] = 'source = %s';
            $args[] = sanitize_text_field((string) $filters['source']);
        }

        if (isset($filters['status'])) {
            $wheres[] = 'status = %d';
            $args[] = (int) $filters['status'];
        }

        if (isset($filters['min_priority'])) {
            $wheres[] = 'priority >= %d';
            $args[] = (int) $filters['min_priority'];
        }

        $where_sql = implode(' AND ', $wheres);
        $offset    = max(0, ($page - 1) * $per_page);

        $args[] = (int) $per_page;
        $args[] = (int) $offset;

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table}
             WHERE {$where_sql}
             ORDER BY status ASC, priority DESC, created_at DESC
             LIMIT %d OFFSET %d",
            ...$args
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Cleanup old notifications.
     *
     * @since 1.6.2
     * @param int $days Retention days.
     * @return int|false Rows affected or false on error.
     */
    public function cleanup_old(int $days = 90) {
        global $wpdb;

        /**
         * Multisite: enforce network retention (Pro policy).
         */
        if (function_exists('get_site_option') && is_multisite()) {
            $policy = get_site_option('nh_network_policy', []);
            if (!empty($policy['retention_days'])) {
                $days = (int) $policy['retention_days'];
            }
        }

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table}
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                (int) $days
            )
        );
    }
}