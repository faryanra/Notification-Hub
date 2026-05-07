<?php
/**
 * NH_Table_Query
 *
 * Query helpers for dashboard table.
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Table_Query {
    /**
     * @var array<int,string>
     */
    private const HIDDEN_TYPES = ['dispatch_check', 'email_sent'];

    private static function hidden_types_sql(): string {
        $types = array_map('sanitize_key', self::HIDDEN_TYPES);
        $quoted = array_map(static function (string $type): string {
            return "'" . esc_sql($type) . "'";
        }, $types);

        return 'type NOT IN (' . implode(', ', $quoted) . ')';
    }

    /**
     * Get counts for dashboard views.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_counts() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $hidden = self::hidden_types_sql();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return [
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            'all'       => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$hidden}"),
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            'unread'    => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$hidden} AND read_at IS NULL AND status IN (0,3)"),
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            'archived'  => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$hidden} AND status = 1"),
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            'important' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$hidden} AND status = 3"),
        ];
    }

    /**
     * Fetch notifications for dashboard table.
     *
     * @since 1.0.0
     * @param array $args Query args.
     * @return array{items: array, total: int}
     */
    public static function get_notifications($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $defaults = [
            'status_filter'      => 'all',
            'search'             => '',
            'filter_source'      => '',
            'filter_type'        => '',
            'filter_priority'    => '',
            'filter_created'     => '',
            'filter_read_status' => '',
            'orderby'            => 'created_at',
            'order'              => 'DESC',
            'per_page'           => 20,
            'paged'              => 1,
        ];
        $args = wp_parse_args($args, $defaults);

        $where  = 'WHERE 1=1 AND ' . self::hidden_types_sql();
        $params = [];

        if ($args['status_filter'] === 'unread') {
            $where .= ' AND read_at IS NULL AND status IN (0,3)';
        } elseif ($args['status_filter'] === 'archived') {
            $where .= ' AND status = 1';
        } elseif ($args['status_filter'] === 'important') {
            $where .= ' AND status = 3';
        }

        if ($args['search'] !== '') {
            $where .= ' AND (source LIKE %s OR title LIKE %s OR message LIKE %s)';
            $like = '%' . $wpdb->esc_like((string) $args['search']) . '%';
            array_push($params, $like, $like, $like);
        }

        if ($args['filter_source'] !== '') {
            $where    .= ' AND source = %s';
            $params[] = (string) $args['filter_source'];
        }

        if ($args['filter_type'] !== '') {
            $where    .= ' AND type = %s';
            $params[] = (string) $args['filter_type'];
        }

        if ($args['filter_priority'] !== '') {
            $where    .= ' AND CAST(priority AS SIGNED) = %d';
            $params[] = (int) $args['filter_priority'];
        }

        if ($args['filter_created'] !== '') {
            switch ($args['filter_created']) {
                case 'today':
                    $where .= ' AND DATE(created_at) = CURDATE()';
                    break;
                case 'yesterday':
                    $where .= ' AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
                    break;
                case 'last_7_days':
                    $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'last_30_days':
                    $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'last_year':
                    $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
            }
        }

        if ($args['filter_read_status'] !== '') {
            if ($args['filter_read_status'] === 'read') {
                $where .= ' AND read_at IS NOT NULL';
            } elseif ($args['filter_read_status'] === 'unread') {
                $where .= ' AND read_at IS NULL';
            } elseif ($args['filter_read_status'] === 'important') {
                $where .= ' AND status = 3';
            }
        }

        $count_sql = "SELECT COUNT(*) FROM {$table} {$where}";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = empty($params)
            ? (int) $wpdb->get_var($count_sql)
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            : (int) $wpdb->get_var($wpdb->prepare($count_sql, $params));

        $per_page = max(1, (int) $args['per_page']);
        $paged    = max(1, (int) $args['paged']);
        $offset   = ($paged - 1) * $per_page;

        $allowed_orderby = [
            'id'         => 'id',
            'title'      => 'title',
            'created_at' => 'created_at',
            'source'     => 'source',
            'priority'   => 'priority',
            'type'       => 'type',
            'status'     => 'status',
            'read_at'    => 'read_at',
        ];
        $orderby_key = sanitize_key((string) $args['orderby']);
        $orderby     = $allowed_orderby[$orderby_key] ?? 'created_at';

        $order = (isset($args['order']) && strtoupper((string) $args['order']) === 'ASC') ? 'ASC' : 'DESC';

        $query_sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        array_push($params, $per_page, $offset);

        $items = empty($params)
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            ? $wpdb->get_results($query_sql)
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            : $wpdb->get_results($wpdb->prepare($query_sql, $params));

        return [
            'items' => $items ?: [],
            'total' => $total,
        ];
    }
}

