<?php
// Dashboard Controller & Table Logic (fixed counts + filters + URLs)

if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class NH_Dashboard {
    protected $registry;

    public function __construct($registry) {
        $this->registry = $registry;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_nh-dashboard') return;

        wp_enqueue_style(
            'nh-notifications',
            NH_PLUGIN_URL . 'assets/css/notifications.css',
            [],
            NH_VERSION
        );

        wp_enqueue_script(
            'nh-dashboard',
            NH_PLUGIN_URL . 'assets/js/dashboard.js',
            ['jquery'],
            NH_VERSION,
            true
        );

        wp_localize_script('nh-dashboard', 'nhAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'notification-hub'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1>';

        $status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
        $table = new NH_Dashboard_Table($status);
        $table->prepare_items();

        echo '<div class="nh-dashboard-header">';
        $views = $table->get_views();

        if (!empty($views)) {
            echo '<ul class="subsubsub">';
            $last = array_key_last($views);
            foreach ($views as $key => $view) {
                echo '<li>' . $view . ($key !== $last ? ' | ' : '') . '</li>';
            }
            echo '</ul>';
        }

        echo '<div class="search-box">';
        $table->search_box(__('Search Notifications', 'notification-hub'), 'nh-search');
        echo '</div>';
        echo '</div>';

        echo '<form method="post">';
        wp_nonce_field('bulk-notifications');
        echo '<input type="hidden" name="page" value="nh-dashboard" />';
        $table->display();
        echo '</form>';

        echo '</div>';
    }
}

class NH_Dashboard_Table extends WP_List_Table {
    private $per_page = 20;
    private $status_filter;
    private $counts = ['all' => 0, '0' => 0, '1' => 0];

    public function __construct($status_filter = 'all') {
        parent::__construct([
            'singular' => 'notification',
            'plural'   => 'notifications',
        ]);

        $this->status_filter = $status_filter;
        $this->calculate_counts();
    }

    private function calculate_counts() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $this->counts['all'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $this->counts['0']   = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 0");
        $this->counts['1']   = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 1");
    }

    protected function get_views() {
        $base = admin_url('admin.php?page=nh-dashboard');

        return [
            'all'      => '<a href="' . $base . '" ' . ($this->status_filter === 'all' ? 'class="current"' : '') . '>All <span class="count">(' . $this->counts['all'] . ')</span></a>',
            'active'   => '<a href="' . $base . '&filter_status=0" ' . ($this->status_filter === '0' ? 'class="current"' : '') . '>Active <span class="count">(' . $this->counts['0'] . ')</span></a>',
            'archived' => '<a href="' . $base . '&filter_status=1" ' . ($this->status_filter === '1' ? 'class="current"' : '') . '>Archived <span class="count">(' . $this->counts['1'] . ')</span></a>',
        ];
    }

    protected function get_bulk_actions() {
        return $this->status_filter === '1'
            ? ['unarchive' => __('Unarchive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')]
            : ['archive' => __('Archive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')];
    }

    public function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'id'         => 'ID',
            'source'     => 'Source',
            'type'       => 'Type',
            'title'      => 'Title',
            'message'    => 'Message',
            'status'     => 'Status',
            'created_at' => 'Created',
            'actions'    => 'Actions'
        ];
    }

    protected function column_cb($item) {
        return '<input type="checkbox" name="ids[]" value="' . intval($item->id) . '" />';
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'status':
                return $item->status == 0 ? '<span class="nh-badge nh-badge--ok">Active</span>' : '<span class="nh-badge nh-badge--archived">Archived</span>';
            case 'message':
                return esc_html(wp_strip_all_tags(mb_strimwidth($item->message, 0, 100, '…')));
            case 'created_at':
                return esc_html(mysql2date('Y-m-d H:i', $item->created_at));
            case 'actions':
                $id = intval($item->id);
                $view_nonce = wp_create_nonce('nh_view_' . $id);
                $toggle_nonce = wp_create_nonce('nh_toggle_' . $id);
                $do = $item->status == 1 ? 'unarchive' : 'archive';
                $view_btn = sprintf('<a href="#" class="button nh-view" data-id="%d" data-nonce="%s">View</a>', $id, $view_nonce);
                $toggle_url = add_query_arg(['action' => 'nh_toggle_archive', 'id' => $id, 'do' => $do, '_wpnonce' => $toggle_nonce], admin_url('admin-post.php'));
                $toggle_btn = '<a href="' . esc_url($toggle_url) . '" class="button-link">' . ucfirst($do) . '</a>';
                $delete_url = wp_nonce_url(admin_url('admin-post.php?action=nh_delete_notification&id=' . $id), 'nh_delete_' . $id);
                return $view_btn . ' ' . $toggle_btn . ' <a href="' . esc_url($delete_url) . '" class="button-link-delete">Delete</a>';
            default:
                return esc_html($item->$column_name);
        }
    }

    public function prepare_items() {
        $this->process_bulk_action();
        $this->calculate_counts();

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'created_at';
        $order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';
        $search = isset($_REQUEST['s']) ? trim(wp_unslash($_REQUEST['s'])) : '';

        $where = 'WHERE 1=1';
        $params = [];

        if (in_array($this->status_filter, ['0', '1'], true)) {
            $where .= ' AND status = %d';
            $params[] = (int)$this->status_filter;
        }

        if ($search !== '') {
            $where .= ' AND (source LIKE %s OR title LIKE %s OR message LIKE %s)';
            $like = '%' . $wpdb->esc_like($search) . '%';
            array_push($params, $like, $like, $like);
        }

        $total_sql = "SELECT COUNT(*) FROM $table $where";
        $total_items = empty($params) ? $wpdb->get_var($total_sql) : $wpdb->get_var($wpdb->prepare($total_sql, $params));

        $paged = $this->get_pagenum();
        $offset = ($paged - 1) * $this->per_page;

        $query_sql = "SELECT * FROM $table $where ORDER BY $orderby $order LIMIT %d OFFSET %d";
        array_push($params, $this->per_page, $offset);

        $this->items = $wpdb->get_results($wpdb->prepare($query_sql, $params));

        $this->_column_headers = [$this->get_columns(), [], []];
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $this->per_page,
        ]);
    }

    public function process_bulk_action() {
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'bulk-notifications')) return;

        $action = $this->current_action();
        if (!$action) return;

        $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : [];
        if (empty($ids)) return;

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        foreach ($ids as $id) {
            switch ($action) {
                case 'delete':
                    $wpdb->delete($table, ['id' => $id], ['%d']);
                    break;
                case 'archive':
                    $wpdb->update($table, ['status' => 1], ['id' => $id], ['%d'], ['%d']);
                    break;
                case 'unarchive':
                    $wpdb->update($table, ['status' => 0], ['id' => $id], ['%d'], ['%d']);
                    break;
            }
        }
    }
}

add_action('wp_ajax_nh_view_notification', function(){
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Forbidden'], 403);
    }

    $id = intval($_REQUEST['id'] ?? 0);
    $nonce = $_REQUEST['_wpnonce'] ?? ($_REQUEST['nonce'] ?? '');

    if (!$id || !wp_verify_nonce($nonce, 'nh_view_' . $id)) {
        wp_send_json_error(['message' => 'Invalid request'], 400);
    }

    global $wpdb;
    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nh_notifications WHERE id=%d", $id)
    );

    if (!$row) {
        wp_send_json_error(['message' => 'Notification not found'], 404);
    }

    wp_send_json_success([
        'title'      => $row->title,
        'message'    => $row->message,
        'source'     => $row->source,
        'status'     => $row->status,
        'created_at' => mysql2date('Y-m-d H:i', $row->created_at)
    ]);
});
