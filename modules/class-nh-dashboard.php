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

        echo '<div class="wrap">
        <div id="nh-table-loader" style="
            display:none;
            position:absolute;
            top:0; left:0;
            width:100%; height:100%;
            background:rgba(255,255,255,0.7);
            z-index:999;
            align-items:center;
            justify-content:center;
            font-size:20px;
        ">
            <span class="spinner is-active" style="float:none;"></span>
        </div>
        <h1 class="wp-heading-inline">' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1><hr class="wp-header-end">';

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
        // [NH v1.6.0] Filters: min_priority, tags, only_important
        $table->search_box(__('Search Notifications', 'notification-hub'), 'nh-search');
        $table->display();
        echo '</form></div>';

        // Update user's last seen timestamp
        update_user_meta($uid, 'nh_last_seen_at', current_time('mysql'));
    }

    // Mark single notification as Important (GET)
    public function mark_important_action() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Forbidden', 'notification-hub'));
        }

        check_admin_referer('nh_mark_important_' . $_GET['id']);
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $id = intval($_GET['id'] ?? 0);

        if ($id) {
            $wpdb->update($table, ['status' => 3], ['id' => $id], ['%d'], ['%d']);
        }

        wp_redirect(admin_url('admin.php?page=nh-dashboard'));
        exit;
    }

}

// =====================================================
// DELETE HANDLER (Remove Notification)
// =====================================================
add_action('admin_post_nh_delete_notification', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden');
    }

    $id    = intval($_GET['id'] ?? 0);
    $nonce = $_GET['_wpnonce'] ?? '';

    if (!$id || !wp_verify_nonce($nonce, 'nh_delete_' . $id)) {
        wp_die('Invalid request');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $wpdb->delete($table, ['id' => $id], ['%d']);

    wp_redirect(admin_url('admin.php?page=nh-dashboard'));
    exit;
});


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
            'all'       => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"),
            'unread'    => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE read_at IS NULL AND status IN (0,3)"),
            'archived'  => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 1"),
            'important' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 3"),
        ];
    }

    /**
     * Display filter tabs (All / Active / Archived)
     */
    protected function get_views() {

        $base = admin_url('admin.php?page=nh-dashboard');
        $current = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';

        $views = [];

        // All
        $views['all'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url($base),
            ($current === 'all' ? ' class="current"' : ''),
            esc_html__('All', 'notification-hub'),
            (int) ($this->counts['all'] ?? 0)
        );

        // UNREAD (read_at IS NULL)
        $views['unread'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url(add_query_arg(['filter_status' => 'unread'], $base)),
            ($current === 'unread' ? ' class="current"' : ''),
            esc_html__('Unread', 'notification-hub'),
            (int) ($this->counts['unread'] ?? 0)
        );

        // Archived (status=1)
        $views['archived'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url(add_query_arg(['filter_status' => 'archived'], $base)),
            ($current === 'archived' ? ' class="current"' : ''),
            esc_html__('Archived', 'notification-hub'),
            (int) ($this->counts['archived'] ?? 0)
        );

        // Important (status=3)
        $views['important'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url(add_query_arg(['filter_status' => 'important'], $base)),
            ($current === 'important' ? ' class="current"' : ''),
            esc_html__('Important', 'notification-hub'),
            (int) ($this->counts['important'] ?? 0)
        );

        return $views;
    }

    /**
     * Define bulk actions (Archive / Unarchive / Delete / Mark as Read)
     */
    protected function get_bulk_actions() {
        $actions = $this->status_filter === '1'
            ? ['unarchive' => __('Unarchive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')]
            : ['archive' => __('Archive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')];

        $actions['nh_bulk_mark_read'] = __('Mark as Read', 'notification-hub');
        $actions['mark_important']   = __('Mark Important', 'notification-hub');
        $actions['unmark_important'] = __('Remove Important', 'notification-hub');
        return $actions;
    }

    /**
     * Add Export CSV button + Filter UI
     */
    protected function extra_tablenav($which) {
        if ($which === 'top') {

            echo '<div class="alignleft actions">';

            // Min Priority
            $min_pr = isset($_GET['min_priority']) ? (int) $_GET['min_priority'] : '';
            echo '<input type="number" min="0" max="100" name="min_priority" value="' . esc_attr($min_pr) . '" placeholder="' . esc_attr__('Min Priority','notification-hub') . '" style="width:100px" /> ';

            // Tags
            $tags_q = isset($_GET['tags']) ? sanitize_text_field($_GET['tags']) : '';
            echo '<input type="text" name="tags" value="' . esc_attr($tags_q) . '" placeholder="' . esc_attr__('Tags (comma separated)','notification-hub') . '" style="width:180px" /> ';

            // Only Important
            $only_imp = !empty($_GET['only_important']);
            echo '<label style="margin-right:8px;"><input type="checkbox" name="only_important" value="1" ' . checked($only_imp, true, false) . ' /> ' . esc_html__('Only Important', 'notification-hub') . '</label>';

            submit_button(__('Filter'), 'secondary', 'filter_action', false);

            echo '</div>';

            // Export CSV button
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
            'cb'        => '<input type="checkbox" />',
            'id'        => __('ID', 'notification-hub'),
            'title'     => __('Title', 'notification-hub'),
            'view'      => '', 
            'created_at'=> __('Created', 'notification-hub'),
            'source'    => __('Source', 'notification-hub'),
            'type'      => __('Type', 'notification-hub'),
            'tags'      => __('Tags', 'notification-hub'),
            'status'    => __('Status', 'notification-hub'),
            'priority'  => __('Priority', 'notification-hub'),
        ];
        return $cols;
    }

    /**
     * Define sortable columns
     * [NH v1.6.0] added priority sorting
     */
    public function get_sortable_columns() {
        $sortable = [
            'created_at' => ['created_at', true],
            'priority'   => ['priority', false], // [NH v1.6.0]
        ];
        return $sortable;
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
                // Unread
                if (empty($item->read_at)) {
                    return '<span class="nh-status-badge nh-status-unread">Unread</span>';
                }

                // Important
                if ((int)$item->status === 3) {
                    return '<span class="nh-status-badge nh-status-important">Important</span>';
                }

                // Archived
                if ((int)$item->status === 1) {
                    return '<span class="nh-status-badge nh-status-archived">Archived</span>';
                }

                // Read → no badge
                return '';


            case 'message':
                return esc_html(wp_strip_all_tags(mb_strimwidth($item->message ?? '', 0, 100, '…')));

            case 'created_at':
                return esc_html($item->created_at);

            case 'priority':
                return $this->column_priority($item);

            case 'tags':
                return $this->column_tags($item);

            default:
                // Undefined property
                $val = isset($item->$column_name) ? $item->$column_name : '';
                if (is_scalar($val)) {
                    return esc_html((string)$val);
                }
                return esc_html(wp_json_encode($val));
        }
    }

    /**
     * Render Title column:
     * - Eye icon (modal preview)
     * - Title (linked to admin object if possible)
     * - Row actions under title (Mark Read / Unread / Important)
     */
    protected function column_title($item) {
        $id = (int) $item->id;

        /* ---------------------------------------------------------
        Unread Dot Indicator
        --------------------------------------------------------- */
        $dot = empty($item->read_at)
            ? '<span class="nh-dot-unread">●</span>'
            : '';

        /* ---------------------------------------------------------
        Title + Admin Link
        --------------------------------------------------------- */
        $title = esc_html($item->title ?: __('(no title)', 'notification-hub'));

        if ($admin_link = $this->make_admin_link_from_context($item)) {
            $title_html = '<a class="nh-title-link" href="' . esc_url($admin_link) . '">' . $title . '</a>';
        } else {
            $title_html = '<span class="nh-title-link">' . $title . '</span>';
        }

        /* ---------------------------------------------------------
        ROW ACTIONS (AJAX version)
        --------------------------------------------------------- */
        $actions = [];

        /* 1) Mark Read / Unread */
        if (empty($item->read_at)) {
            // unread → show "Mark as Read"
            $actions[] = sprintf(
                '<a href="#" class="nh-mark-read" data-id="%d">%s</a>',
                $id,
                __('Mark as Read', 'notification-hub')
            );
        } else {
            // read → show "Mark as Unread"
            $actions[] = sprintf(
                '<a href="#" class="nh-mark-unread" data-id="%d">%s</a>',
                $id,
                __('Mark as Unread', 'notification-hub')
            );
        }

        /* 2) Mark Important / Remove Important */
        if ((int)$item->status === 3) {
            // important → allow remove
            $actions[] = sprintf(
                '<a href="#" class="nh-unmark-important" data-id="%d">%s</a>',
                $id,
                __('Remove Important', 'notification-hub')
            );
        } else {
            // normal → allow mark important
            $actions[] = sprintf(
                '<a href="#" class="nh-mark-important" data-id="%d">%s</a>',
                $id,
                __('Mark as Important', 'notification-hub')
            );
        }

        /* 3) Delete (this one is NOT ajax) */
        $actions[] = sprintf(
            '<a style="color:#d63638" href="%s">%s</a>',
            wp_nonce_url(
                admin_url('admin-post.php?action=nh_delete_notification&id=' . $id),
                'nh_delete_' . $id
            ),
            __('Remove', 'notification-hub')
        );

        $actions_html = '<div class="row-actions">' . implode(' | ', $actions) . '</div>';

        /* OUTPUT */
        return '
            <div class="nh-title-wrapper">
                ' . $dot . $title_html . '
                ' . $actions_html . '
            </div>
        ';
    }

    protected function column_view($item) {
        return sprintf(
            '<span class="nh-eye nh-open-modal" data-id="%d" data-nonce="%s">
                <span class="dashicons dashicons-visibility"></span>
            </span>',
            (int)$item->id,
            esc_attr(wp_create_nonce('nh_view_' . $item->id)),
            esc_attr__('Preview', 'notification-hub')
        );
    }

    // [NH v1.6.0] Priority & Tags rendering helpers/columns
    protected function render_tags_pills($tagsJson) {
        $out = '';
        if ($tagsJson) {
            $arr = json_decode($tagsJson, true);
            if (is_array($arr)) {
                foreach ($arr as $t) {
                    $t = esc_html($t);
                    $out .= '<span class="nh-tag-pill">'.$t.'</span> ';
                }
            }
        }
        return $out ?: '—';
    }

    public function column_priority($item) {
        return isset($item->priority) ? (int)$item->priority : 50;
    }

    public function column_tags($item) {
        return $this->render_tags_pills($item->tags ?? null);
    }

    private function make_admin_link_from_context($item) {
        $ctx = is_string($item->context) ? json_decode($item->context, true) : $item->context;

        if (!is_array($ctx) || empty($ctx)) {
            return null;
        }

        // WooCommerce Order
        if (!empty($ctx['order_id'])) {
            return admin_url('post.php?post=' . intval($ctx['order_id']) . '&action=edit');
        }

        // Comment
        if (!empty($ctx['comment_id'])) {
            return admin_url('comment.php?action=editcomment&c=' . intval($ctx['comment_id']));
        }

        // Post/Page
        if (!empty($ctx['post_id'])) {
            return admin_url('post.php?post=' . intval($ctx['post_id']) . '&action=edit');
        }

        // Contact Form 7 Form
        if (!empty($ctx['cf7_form_id'])) {
            return admin_url('admin.php?page=wpcf7&post=' . intval($ctx['cf7_form_id']) . '&action=edit');
        }

        return null;
    }

    /**
     * Render table rows with new-row highlighting
     */
    public function display_rows() {
        foreach ($this->items as $item) {

            // Detect "new" rows based on last seen timestamp
            $is_new = (strtotime($item->created_at) > strtotime($this->prev_seen));

            // Detect unread rows based on read_at NULL
            $is_unread = empty($item->read_at);

            // Build TR classes dynamically
            $classes = [];
            if ($is_new) {
                $classes[] = 'nh-row-new';
            }
            if ($is_unread) {
                $classes[] = 'nh-unread-row';
            }

            // Convert classes array to string
            $tr_class = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';

            echo '<tr data-id="' . $item->id . '" ' . $tr_class . ' data-created="' . esc_attr($item->created_at) . '">';

            list($columns, $hidden, $sortable, $primary) = $this->get_column_info();

            foreach ($columns as $column_name => $column_display_name) {

                if ($column_name === 'cb') {
                    echo '<th scope="row" class="check-column">';
                    echo $this->column_cb($item);
                    echo '</th>';
                    continue;
                }

                $classes = "$column_name column-$column_name";
                if ($primary === $column_name) {
                    $classes .= ' has-row-actions column-primary';
                }

                printf('<td class="%s">', esc_attr($classes));

                // Use column_<name> method if exists
                if (method_exists($this, 'column_' . $column_name)) {
                    echo call_user_func([$this, 'column_' . $column_name], $item);
                } else {
                    echo $this->column_default($item, $column_name);
                }

                // Toggle button for mobile (WordPress default behavior)
                if ($primary === $column_name) {
                    echo '<button type="button" class="toggle-row"><span class="screen-reader-text">'
                        . esc_html__('Show more details', 'notification-hub')
                        . '</span></button>';
                }

                echo '</td>';
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

        // [NH v1.6.0] Filters
        $min_priority = isset($_REQUEST['min_priority']) ? (int) $_REQUEST['min_priority'] : null;

        $tags_filter = [];
        if (!empty($_REQUEST['tags'])) {
            $raw = sanitize_text_field(wp_unslash($_REQUEST['tags']));
            $parts = preg_split('/[\s,]+/', $raw);
            $tags_filter = array_filter(array_map('trim', $parts));
        }

        $only_important = !empty($_REQUEST['only_important']);

        if ($min_priority !== null) { 
            $where .= ' AND priority >= %d'; 
            $params[] = $min_priority; 
        }
        if ($only_important) { 
            $where .= ' AND status = 3'; 
        }

        // tags LIKE "%\"foo\"%" trick for JSON exact term
        foreach ($tags_filter as $t) {
            $where .= ' AND tags LIKE %s';
            $params[] = '%"'.like_escape($t).'"%';
        }

        // Status filter (0=Active, 1=Archived, 3=Important)
        if ($this->status_filter === 'unread') {
            $where .= " AND read_at IS NULL AND status IN (0,3) ";
        }
        elseif ($this->status_filter === 'archived') {
            $where .= " AND status = 1 ";
        }
        elseif ($this->status_filter === 'important') {
            $where .= " AND status = 3 ";
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
        $in = implode(',', $ids);

        switch ($action) {
            case 'delete':
                $wpdb->query("DELETE FROM {$table} WHERE id IN ({$in})");
                break;

            case 'archive':
                $wpdb->query("UPDATE {$table} SET status = 1 WHERE id IN ({$in})");
                break;

            case 'unarchive':
                $wpdb->query("UPDATE {$table} SET status = 0 WHERE id IN ({$in})");
                break;

            case 'nh_bulk_mark_read':
                $wpdb->query("UPDATE {$table} SET read_at = NOW() WHERE id IN ({$in})");
                break;

            case 'mark_important':
                $wpdb->query("UPDATE {$table} SET status = 3 WHERE id IN ({$in})");
                break;

            case 'unmark_important':
                $wpdb->query("UPDATE {$table} SET status = 0 WHERE id IN ({$in}) AND status = 3");
                break;
        }

        if ('mark_important' === $this->current_action()) {
            $ids = array_map('intval', (array)($_REQUEST['ids'] ?? []));
            if ($ids) {
                $in = implode(',', $ids);
                $wpdb->query("UPDATE {$table} SET status = 3 WHERE id IN ({$in})");
            }
        }
        if ('unmark_important' === $this->current_action()) {
            $ids = array_map('intval', (array)($_REQUEST['ids'] ?? []));
            if ($ids) {
                $in = implode(',', $ids);
                $wpdb->query("UPDATE {$table} SET status = 0 WHERE id IN ({$in}) AND status = 3");
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