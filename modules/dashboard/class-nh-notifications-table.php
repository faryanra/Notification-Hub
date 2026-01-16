<?php
/**
 * Notifications Table (WP_List_Table)
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once __DIR__ . '/class-nh-table-query.php';
require_once __DIR__ . '/class-nh-table-columns.php';
require_once __DIR__ . '/class-nh-table-bulk-actions.php';
require_once __DIR__ . '/class-nh-table-filters.php';

class NH_Notifications_Table extends WP_List_Table {

    private $status_filter;
    private $prev_seen;
    private $counts;
    private $per_page = 20;

    public function __construct($status_filter = 'all', $prev_seen = '1970-01-01 00:00:00') {
        parent::__construct([
            'singular' => 'notification',
            'plural'   => 'notifications',
        ]);

        $this->status_filter = $status_filter;
        $this->prev_seen = $prev_seen;
        $this->counts = NH_Table_Query::get_counts();
    }

    /**
     * Get filter tabs (All, Unread, Archived, Important)
     */
    protected function get_views() {
        $base = admin_url('admin.php?page=nh-dashboard');
        $current = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
        $views = [];

        foreach (['all', 'unread', 'archived', 'important'] as $key) {
            $url = ($key === 'all') ? $base : add_query_arg(['filter_status' => $key], $base);
            $label = ucfirst($key);
            $count = (int) ($this->counts[$key] ?? 0);
            $class = ($current === $key) ? ' class="current"' : '';
            
            $views[$key] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url($url),
                $class,
                esc_html__($label, 'notification-hub'),
                $count
            );
        }

        return $views;
    }

    /**
     * Define bulk actions
     */
    protected function get_bulk_actions() {
        $actions = $this->status_filter === '1'
            ? ['unarchive' => __('Unarchive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')]
            : ['archive' => __('Archive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')];

        $actions['nh_bulk_mark_read'] = __('Mark as Read', 'notification-hub');
        $actions['nh_bulk_mark_unread'] = __('Mark as Unread', 'notification-hub');
        $actions['mark_important'] = __('Mark Important', 'notification-hub');
        $actions['unmark_important'] = __('Remove Important', 'notification-hub');

        return $actions;
    }

    /**
     * Render extra controls (filters + export)
     */
    protected function extra_tablenav($which) {
        if ($which !== 'top') return;
        NH_Table_Filters::render();
    }

    /**
     * Define table columns
     */
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

    /**
     * Define sortable columns
     */
    protected function get_sortable_columns() {
        return [
            'id'       => ['id', false],
            'title'    => ['title', false],
            'created'  => ['created_at', true],
            'source'   => ['source', false],
            'priority' => ['priority', false],
        ];
    }

    /**
     * Column rendering delegation
     */
    protected function column_cb($item) { return NH_Table_Columns::column_cb($item); }
    protected function column_id($item) { return NH_Table_Columns::column_id($item); }
    protected function column_title($item) { return NH_Table_Columns::column_title($item); }
    protected function column_view($item) { return NH_Table_Columns::column_view($item); }
    protected function column_created($item) { return NH_Table_Columns::column_created($item); }
    protected function column_source($item) { return NH_Table_Columns::column_source($item); }
    protected function column_type($item) { return NH_Table_Columns::column_type($item); }
    protected function column_priority($item) { return NH_Table_Columns::column_priority($item); }
    protected function column_status($item) { return NH_Table_Columns::column_status($item); }

    /**
     * Render table row with custom classes
     */
    public function single_row($item) {
        $classes = [];
        
        if (empty($item->read_at)) {
            $classes[] = 'nh-unread-row';
        }
        
        if (strtotime($item->created_at) > strtotime($this->prev_seen)) {
            $classes[] = 'nh-row-new';
        }
        
        $class_attr = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
        echo '<tr' . $class_attr . ' data-id="' . esc_attr($item->id) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    /**
     * Prepare table items
     */
    public function prepare_items() {
        $this->process_bulk_action();
        $this->counts = NH_Table_Query::get_counts();

        $params = [
            'status_filter'     => $this->status_filter,
            'search'            => isset($_REQUEST['s']) ? trim(wp_unslash($_REQUEST['s'])) : '',
            'filter_source'     => isset($_GET['filter_source']) ? sanitize_text_field($_GET['filter_source']) : '',
            'filter_type'       => isset($_GET['filter_type']) ? sanitize_text_field($_GET['filter_type']) : '',
            'filter_priority'   => isset($_GET['filter_priority']) ? sanitize_text_field($_GET['filter_priority']) : '',
            'filter_created'    => isset($_GET['filter_created']) ? sanitize_text_field($_GET['filter_created']) : '',
            'filter_read_status'=> isset($_GET['filter_read_status']) ? sanitize_text_field($_GET['filter_read_status']) : '',
            'orderby'           => isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'created_at',
            'order'             => (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC',
            'per_page'          => $this->per_page,
            'paged'             => $this->get_pagenum(),
        ];

        $result = NH_Table_Query::get_notifications($params);

        $this->items = $result['items'];
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $result['total'],
            'per_page'    => $this->per_page,
        ]);
    }

    /**
     * Process bulk actions
     */
    protected function process_bulk_action() {
        $action = $this->current_action();
        if (!$action) return;

        $ids = isset($_REQUEST['ids']) ? array_map('intval', (array) $_REQUEST['ids']) : [];
        if (empty($ids)) return;

        check_admin_referer('bulk-notifications');
        NH_Table_Bulk_Actions::process($action, $ids);

        wp_safe_redirect(remove_query_arg(['action', 'action2', 'ids', '_wpnonce', '_wp_http_referer']));
        exit;
    }
}
