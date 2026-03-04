<?php
/**
 * NH_Notifications_Table
 *
 * Notifications table implementation based on WP_List_Table.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once __DIR__ . '/Table/Query.php';
require_once __DIR__ . '/Table/Columns.php';
require_once __DIR__ . '/Table/BulkActions.php';
require_once __DIR__ . '/Table/Filters.php';

class NH_Notifications_Table extends WP_List_Table {
    private $status_filter;
    private $prev_seen;
    private $counts = [];
    private $per_page = 20;

    public function __construct($status_filter = 'all', $prev_seen = '1970-01-01 00:00:00') {
        parent::__construct([
            'singular' => 'notification',
            'plural'   => 'notifications',
        ]);

        $this->status_filter = sanitize_key((string) $status_filter);
        $this->prev_seen     = (string) $prev_seen;
        $this->counts        = NH_Table_Query::get_counts();
    }

    protected function get_views() {
        $base = admin_url('admin.php?page=nh-dashboard');

        $current = isset($_GET['filter_status'])
            ? sanitize_key(wp_unslash($_GET['filter_status']))
            : 'all';

        $allowed_status = ['all', 'unread', 'archived', 'important'];
        if (!in_array($current, $allowed_status, true)) {
            $current = 'all';
        }

        $views = [];

        $view_map = [
            'all'       => __('All', 'notification-hub'),
            'unread'    => __('Unread', 'notification-hub'),
            'archived'  => __('Archived', 'notification-hub'),
            'important' => __('Important', 'notification-hub'),
        ];

        foreach ($view_map as $key => $label) {
            $url   = ($key === 'all') ? $base : add_query_arg(['filter_status' => $key], $base);
            $count = (int) ($this->counts[$key] ?? 0);
            $class = ($current === $key) ? ' class="current"' : '';

            $views[$key] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url($url),
                $class,
                esc_html($label),
                $count
            );
        }

        return $views;
    }

    protected function get_bulk_actions() {
        $actions = [];

        if ($this->status_filter === 'archived') {
            $actions['unarchive'] = __('Unarchive', 'notification-hub');
        } else {
            $actions['archive'] = __('Archive', 'notification-hub');
        }

        $actions['delete'] = __('Delete', 'notification-hub');

        $actions['nh_bulk_mark_read']   = __('Mark as read', 'notification-hub');
        $actions['nh_bulk_mark_unread'] = __('Mark as unread', 'notification-hub');
        $actions['mark_important']      = __('Mark important', 'notification-hub');
        $actions['unmark_important']    = __('Remove important', 'notification-hub');

        return $actions;
    }

    protected function extra_tablenav($which) {
        if ($which !== 'top') {
            return;
        }

        NH_Table_Filters::render();
    }

    public function get_columns() {
        return [
            'cb'       => '<input type="checkbox" />',
            'id'       => __('ID', 'notification-hub'),
            'title'    => __('Title', 'notification-hub'),
            'view'     => '',
            'created'  => __('Created', 'notification-hub'),
            'source'   => __('Source', 'notification-hub'),
            'type'     => __('Type', 'notification-hub'),
            'priority' => __('Priority', 'notification-hub'),
            'status'   => __('Status', 'notification-hub'),
        ];
    }

    protected function get_sortable_columns() {
        return [
            'id'       => ['id', false],
            'title'    => ['title', false],
            'created'  => ['created_at', true],
            'source'   => ['source', false],
            'priority' => ['priority', false],
        ];
    }

    protected function column_cb($item) { return NH_Table_Columns::column_cb($item); }
    protected function column_id($item) { return NH_Table_Columns::column_id($item); }
    protected function column_title($item) { return NH_Table_Columns::column_title($item); }
    protected function column_view($item) { return NH_Table_Columns::column_view($item); }
    protected function column_created($item) { return NH_Table_Columns::column_created($item); }
    protected function column_source($item) { return NH_Table_Columns::column_source($item); }
    protected function column_type($item) { return NH_Table_Columns::column_type($item); }
    protected function column_priority($item) { return NH_Table_Columns::column_priority($item); }
    protected function column_status($item) { return NH_Table_Columns::column_status($item); }

    public function single_row($item) {
        $classes = [];

        if (empty($item->read_at)) {
            $classes[] = 'nh-unread-row';
        }

        if (!empty($item->created_at) && strtotime((string) $item->created_at) > strtotime((string) $this->prev_seen)) {
            $classes[] = 'nh-row-new';
        }

        $class_attr = !empty($classes) ? ' class="' . esc_attr(implode(' ', $classes)) . '"' : '';

        echo '<tr' . $class_attr . ' data-id="' . esc_attr($item->id) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    public function prepare_items() {
        $this->process_bulk_action();
        $this->counts = NH_Table_Query::get_counts();

        $params = [
            'status_filter'      => $this->status_filter,
            'search'             => isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '',
            'filter_source'      => isset($_GET['filter_source']) ? sanitize_key(wp_unslash($_GET['filter_source'])) : '',
            'filter_type'        => isset($_GET['filter_type']) ? sanitize_key(wp_unslash($_GET['filter_type'])) : '',
            'filter_priority'    => isset($_GET['filter_priority']) ? sanitize_key(wp_unslash($_GET['filter_priority'])) : '',
            'filter_created'     => isset($_GET['filter_created']) ? sanitize_text_field(wp_unslash($_GET['filter_created'])) : '',
            'filter_read_status' => isset($_GET['filter_read_status']) ? sanitize_key(wp_unslash($_GET['filter_read_status'])) : '',
            'orderby'            => isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'created_at',
            'order'              => (isset($_GET['order']) && strtolower((string) wp_unslash($_GET['order'])) === 'asc') ? 'ASC' : 'DESC',
            'per_page'           => $this->per_page,
            'paged'              => $this->get_pagenum(),
        ];

        $result = NH_Table_Query::get_notifications($params);

        $this->items = $result['items'] ?? [];
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => (int) ($result['total'] ?? 0),
            'per_page'    => $this->per_page,
        ]);
    }

    protected function process_bulk_action() {
        $action = $this->current_action();
        if (!$action) {
            return;
        }

        $ids = isset($_REQUEST['ids']) ? array_map('intval', (array) wp_unslash($_REQUEST['ids'])) : [];
        $ids = array_values(array_filter($ids));

        if (empty($ids)) {
            return;
        }

        check_admin_referer('bulk-notifications');

        if (!class_exists('NH_Table_Bulk_Actions') || !method_exists('NH_Table_Bulk_Actions', 'process')) {
            return;
        }

        NH_Table_Bulk_Actions::process($action, $ids);

        wp_safe_redirect(remove_query_arg(['action', 'action2', 'ids', '_wpnonce', '_wp_http_referer']));
        exit;
    }
}
