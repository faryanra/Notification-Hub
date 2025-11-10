<?php
// =====================================================
// Notification Hub — Dashboard Controller & Table Logic
// =====================================================

if (!defined('ABSPATH')) exit;

// Load WP_List_Table if not already available
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * NH_Dashboard
 * - Handles the main admin dashboard page of Notification Hub
 * - Loads assets, renders the WP_List_Table
 * - Provides "Mark as Read" action
 */
class NH_Dashboard {
    protected $registry;

    public function __construct($registry) {
        $this->registry = $registry;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_nh_mark_read', [$this, 'mark_read_action']);
    }

    /**
     * Enqueue styles and scripts for the dashboard page
     */
    public function enqueue_assets($hook) {
        // Load assets only on Notification Hub dashboard
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

        // Pass data to JS: AJAX URL + global nonce
        wp_localize_script('nh-dashboard', 'nhAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('nh_ajax_nonce'),
        ]);

        // Track user's last seen time (for highlighting new notifications)
        $uid = get_current_user_id();
        $prev_seen = get_user_meta($uid, 'nh_last_seen_at', true) ?: '1970-01-01 00:00:00';
        $now = current_time('mysql');

        wp_localize_script('nh-dashboard', 'nhSeen', [
            'prev' => $prev_seen,
        ]);

        update_user_meta($uid, 'nh_last_seen_at', $now);
    }

    /**
     * Render the main Notifications Dashboard page
     */
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'notification-hub'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // Mark all unseen notifications as read when opening the dashboard
        $wpdb->query("UPDATE {$table} SET read_at = NOW() WHERE read_at IS NULL");

        echo '<div class="wrap"><h1 class="wp-heading-inline">' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1><hr class="wp-header-end">';

        // Detect active filter tab (All / Active / Archived)
        $status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';

        $uid = get_current_user_id();
        $prev_seen = get_user_meta($uid, 'nh_last_seen_at', true) ?: '1970-01-01 00:00:00';

        // Initialize table object
        $table = new NH_Dashboard_Table($status, $prev_seen);
        $table->prepare_items();

        // Render filter tabs
        $views = $table->get_views();
        if (!empty($views)) {
            echo '<ul class="subsubsub">';
            $last = array_key_last($views);
            foreach ($views as $key => $view) {
                echo '<li>' . $view . ($key !== $last ? ' | ' : '') . '</li>';
            }
            echo '</ul>';
        }

        // Render search and table
        echo '<form method="post">';
        wp_nonce_field('bulk-notifications');
        echo '<input type="hidden" name="page" value="nh-dashboard" />';
        $table->search_box(__('Search Notifications', 'notification-hub'), 'nh-search');
        $table->display();
        echo '</form></div>';

        // Update user's last seen timestamp
        update_user_meta($uid, 'nh_last_seen_at', current_time('mysql'));
    }

    /**
     * Handles manual "Mark as Read" (via GET action)
     */
    public function mark_read_action() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'notification-hub'));
        }

        global $wpdb;
        $id = intval($_GET['id'] ?? 0);
        if ($id) {
            $table = $wpdb->prefix . 'nh_notifications';
            $wpdb->update($table, ['read_at' => current_time('mysql')], ['id' => $id], ['%s'], ['%d']);
        }

        wp_redirect(admin_url('admin.php?page=nh-dashboard'));
        exit;
    }
}

/**
 * NH_Dashboard_Table
 * - Extends WP_List_Table
 * - Handles listing, filtering, searching, and bulk actions
 */
class NH_Dashboard_Table extends WP_List_Table {
    private $per_page = 20;
    private $status_filter;
    private $counts = ['all' => 0, '0' => 0, '1' => 0];
    private $prev_seen;

    public function __construct($status_filter = 'all', $prev_seen = '1970-01-01 00:00:00') {
        parent::__construct([
            'singular' => 'notification',
            'plural'   => 'notifications',
        ]);

        $this->status_filter = $status_filter;
        $this->prev_seen = $prev_seen;
        $this->calculate_counts();
    }

    /**
     * Calculate notification counts for each tab
     */
    private function calculate_counts() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $this->counts = [
            'all' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"),
            '0'   => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %d", 0)),
            '1'   => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %d", 1)),
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[NH] Counts: all=' . $this->counts['all'] . ', active=' . $this->counts['0'] . ', archived=' . $this->counts['1']);
        }
    }

    /**
     * Display filter tabs (All / Active / Archived)
     */
    protected function get_views() {
        $base = admin_url('admin.php?page=nh-dashboard');
        return [
            'all'      => '<a href="' . $base . '" ' . ($this->status_filter === 'all' ? 'class="current"' : '') . '>All <span class="count">(' . $this->counts['all'] . ')</span></a>',
            'active'   => '<a href="' . $base . '&filter_status=0" ' . ($this->status_filter === '0' ? 'class="current"' : '') . '>Active <span class="count">(' . $this->counts['0'] . ')</span></a>',
            'archived' => '<a href="' . $base . '&filter_status=1" ' . ($this->status_filter === '1' ? 'class="current"' : '') . '>Archived <span class="count">(' . $this->counts['1'] . ')</span></a>',
        ];
    }

    /**
     * Define bulk actions (Archive / Unarchive / Delete / Mark as Read)
     */
    protected function get_bulk_actions() {
        $actions = $this->status_filter === '1'
            ? ['unarchive' => __('Unarchive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')]
            : ['archive' => __('Archive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')];

        $actions['nh_bulk_mark_read'] = __('Mark as Read', 'notification-hub');
        return $actions;
    }

    /**
     * Add Export CSV button above the table
     */
    protected function extra_tablenav($which) {
        if ($which === 'top') {
            $export_url = wp_nonce_url(admin_url('admin-post.php?action=nh_export_csv'), 'nh_export_csv');
            echo '<div class="alignleft actions" style="margin-left:10px;">';
            echo '<a class="button button-secondary" href="' . esc_url($export_url) . '">' . esc_html__('Export CSV', 'notification-hub') . '</a>';
            echo '</div>';
        }
    }

    /**
     * Define table columns
     */
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

    /**
     * Checkbox column
     */
    protected function column_cb($item) {
        return '<input type="checkbox" name="ids[]" value="' . intval($item->id) . '" />';
    }

    /**
     * Default column rendering logic
     */
    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'status':
                return $item->status == 0
                    ? '<span class="nh-badge nh-badge--ok">Active</span>'
                    : '<span class="nh-badge nh-badge--archived">Archived</span>';

            case 'message':
                return esc_html(wp_strip_all_tags(mb_strimwidth($item->message, 0, 100, '…')));

            case 'created_at':
                return esc_html(mysql2date('Y-m-d H:i', $item->created_at));

            case 'actions':
                $id = intval($item->id);
                $actions = [];

                // View modal trigger
                $actions['view'] = '<a href="#" class="nh-open-modal" data-id="' . $id . '" data-nonce="' . wp_create_nonce('nh_view_' . $id) . '" title="' . esc_attr__('View', 'notification-hub') . '"><span class="dashicons dashicons-visibility"></span></a>';

                // AJAX "Mark as Read"
                $actions['mark_read'] = '<a href="#" class="nh-mark-read-ajax" data-id="' . $id . '" data-nonce="' . wp_create_nonce('nh_ajax_nonce') . '">' . __('Mark as Read', 'notification-hub') . '</a>';

                // Contextual links (Post, Order, Comment, etc.)
                $ctx = is_string($item->context) ? json_decode($item->context, true) : [];
                $links = [];
                if (!empty($ctx['order_id'])) $links[] = '<a href="' . esc_url(admin_url('post.php?post=' . (int)$ctx['order_id'] . '&action=edit')) . '">' . __('Open Order', 'notification-hub') . '</a>';
                if (!empty($ctx['comment_id'])) $links[] = '<a href="' . esc_url(admin_url('comment.php?action=editcomment&c=' . (int)$ctx['comment_id'])) . '">' . __('Open Comment', 'notification-hub') . '</a>';
                if (!empty($ctx['post_id'])) $links[] = '<a href="' . esc_url(admin_url('post.php?post=' . (int)$ctx['post_id'] . '&action=edit')) . '">' . __('Open Post', 'notification-hub') . '</a>';
                if (!empty($ctx['cf7_form_id'])) $links[] = '<a href="' . esc_url(admin_url('admin.php?page=wpcf7&post=' . (int)$ctx['cf7_form_id'] . '&action=edit')) . '">' . __('Open Form', 'notification-hub') . '</a>';

                $context_links = $links
                    ? implode(' | ', $links)
                    : '<span class="dashicons dashicons-info"></span> ' . __('No context', 'notification-hub');

                return implode(' | ', $actions) . '<br>' . $context_links;

            default:
                return esc_html($item->$column_name);
        }
    }

    /**
     * Render table rows with new-row highlighting
     */
    public function display_rows() {
        foreach ($this->items as $item) {
            $is_new = (strtotime($item->created_at) > strtotime($this->prev_seen));
            $tr_class = $is_new ? ' class="nh-row-new"' : '';

            echo '<tr' . $tr_class . ' data-created="' . esc_attr($item->created_at) . '">';
            list($columns) = $this->get_column_info();

            foreach ($columns as $column_name => $column_display_name) {
                if ($column_name === 'cb') {
                    echo '<th scope="row" class="check-column">' . $this->column_cb($item) . '</th>';
                    continue;
                }
                $class = "class='$column_name column-$column_name'";
                echo "<td $class>" . $this->column_default($item, $column_name) . '</td>';
            }

            echo '</tr>';
        }
    }

    /**
     * Prepare data for table rendering
     */
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

        // Status filter
        if (in_array($this->status_filter, ['0', '1'], true)) {
            $where .= ' AND status = %d';
            $params[] = (int)$this->status_filter;
        }

        // Search filter
        if ($search !== '') {
            $where .= ' AND (source LIKE %s OR title LIKE %s OR message LIKE %s)';
            $like = '%' . $wpdb->esc_like($search) . '%';
            array_push($params, $like, $like, $like);
        }

        // Pagination setup
        $total_sql = "SELECT COUNT(*) FROM $table $where";
        $total_items = empty($params)
            ? $wpdb->get_var($total_sql)
            : $wpdb->get_var($wpdb->prepare($total_sql, $params));

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

    /**
     * Process bulk actions (archive, delete, etc.)
     */
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
                case 'nh_bulk_mark_read':
                    $wpdb->update($table, ['read_at' => current_time('mysql')], ['id' => $id], ['%s'], ['%d']);
                    break;
            }
        }
    }
}

// =====================================================
// AJAX Handlers: View Notification + Mark as Read
// =====================================================

// AJAX: View notification details (modal)
add_action('wp_ajax_nh_view_notification', function() {
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

// AJAX: Mark single notification as read
add_action('wp_ajax_nh_mark_read', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Forbidden'], 403);
    }

    check_ajax_referer('nh_ajax_nonce', '_wpnonce');

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if (!$id) {
        wp_send_json_error(['message' => 'Invalid ID'], 400);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $wpdb->update($table, ['read_at' => current_time('mysql')], ['id' => $id]);

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => $wpdb->last_error], 500);
    }

    wp_send_json_success(['id' => $id]);
});
