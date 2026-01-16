<?php
if (!defined('ABSPATH')) exit;

class NH_Table_Query {

    public static function get_counts() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        return [
            'all'      => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"),
            'unread'   => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE read_at IS NULL AND status IN (0,3)"),
            'archived' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 1"),
            'important'=> (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 3"),
        ];
    }

    public static function get_notifications($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $defaults = [
            'status_filter'     => 'all',
            'search'            => '',
            'filter_source'     => '',
            'filter_type'       => '',
            'filter_priority'   => '',
            'filter_created'    => '',
            'filter_read_status'=> '',
            'orderby'           => 'created_at',
            'order'             => 'DESC',
            'per_page'          => 20,
            'paged'             => 1,
        ];
        $args = wp_parse_args($args, $defaults);

        $where = 'WHERE 1=1';
        $params = [];

        // Status filter (از tab بالا)
        if ($args['status_filter'] === 'unread') {
            $where .= ' AND read_at IS NULL AND status IN (0,3)';
        } elseif ($args['status_filter'] === 'archived') {
            $where .= ' AND status = 1';
        } elseif ($args['status_filter'] === 'important') {
            $where .= ' AND status = 3';
        }

        // Search
        if ($args['search'] !== '') {
            $where .= ' AND (source LIKE %s OR title LIKE %s OR message LIKE %s)';
            $like = '%' . $wpdb->esc_like($args['search']) . '%';
            array_push($params, $like, $like, $like);
        }

        // Source filter
        if ($args['filter_source'] !== '') {
            $where .= ' AND source = %s';
            $params[] = $args['filter_source'];
        }

        // Type filter
        if ($args['filter_type'] !== '') {
            $where .= ' AND type = %s';
            $params[] = $args['filter_type'];
        }

        // Priority filter
        if ($args['filter_priority'] !== '') {
            $where .= ' AND CAST(priority AS SIGNED) = %d';
            $params[] = (int) $args['filter_priority'];
        }

        // Created filter
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

        // ✅ Read Status filter (با Important)
        if ($args['filter_read_status'] !== '') {
            if ($args['filter_read_status'] === 'read') {
                $where .= ' AND read_at IS NOT NULL';
            } elseif ($args['filter_read_status'] === 'unread') {
                $where .= ' AND read_at IS NULL';
            } elseif ($args['filter_read_status'] === 'important') {
                $where .= ' AND status = 3';
            }
        }

        // Get total
        $count_sql = "SELECT COUNT(*) FROM {$table} {$where}";
        $total = empty($params) 
            ? (int) $wpdb->get_var($count_sql) 
            : (int) $wpdb->get_var($wpdb->prepare($count_sql, $params));

        // Get items
        $offset = ($args['paged'] - 1) * $args['per_page'];
        $orderby = sanitize_key($args['orderby']);
        $order = $args['order'] === 'ASC' ? 'ASC' : 'DESC';

        $query_sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        array_push($params, $args['per_page'], $offset);

        $items = empty($params)
            ? $wpdb->get_results($query_sql)
            : $wpdb->get_results($wpdb->prepare($query_sql, $params));

        return [
            'items' => $items ?: [],
            'total' => $total,
        ];
    }
}
