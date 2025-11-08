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
        add_action('admin_post_nh_mark_read', [$this, 'mark_read_action']);
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
        echo '<h1 class="wp-heading-inline">' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1>';
        echo '<hr class="wp-header-end">';

        // 🚫 حذف auto-mark (دیگه همه نوتیف‌ها رو نخون)
        // فقط با دکمه‌ی "Mark all as seen" یا call AJAX این کار انجام میشه.

        $status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
        $table = new NH_Dashboard_Table($status);
        $table->prepare_items();

        $views = $table->get_views();
        if (!empty($views)) {
            echo '<ul class="subsubsub">';
            $last = array_key_last($views);
            foreach ($views as $key => $view) {
                echo '<li>' . $view . ($key !== $last ? ' | ' : '') . '</li>';
            }
            echo '</ul>';
        }

        echo '<form method="post">';
        wp_nonce_field('bulk-notifications');
        echo '<input type="hidden" name="page" value="nh-dashboard" />';
        $table->search_box(__('Search Notifications', 'notification-hub'), 'nh-search');
        $table->display();
        echo '</form>';
        echo '</div>';
    }


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
        $actions = $this->status_filter === '1'
            ? ['unarchive' => __('Unarchive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')]
            : ['archive' => __('Archive', 'notification-hub'), 'delete' => __('Delete', 'notification-hub')];
        $actions['nh_bulk_mark_read'] = __('Mark as Read', 'notification-hub');
        return $actions;
    }

    protected function extra_tablenav($which) {
        if ($which === 'top') {
            $export_url = wp_nonce_url(admin_url('admin-post.php?action=nh_export_csv'), 'nh_export_csv');
            echo '<div class="alignleft actions" style="margin-left:10px;">';
            echo '<a class="button button-secondary" href="' . esc_url($export_url) . '">' . esc_html__('Export CSV', 'notification-hub') . '</a>';
            echo '</div>';
        }
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
                return $item->status == 0 ? '<span class="nh-badge nh-badge--ok">NEW</span>' : '<span class="nh-badge nh-badge--archived">Archived</span>';
            case 'message':
                return esc_html(wp_strip_all_tags(mb_strimwidth($item->message, 0, 100, '…')));
            case 'created_at':
                return esc_html(mysql2date('Y-m-d H:i', $item->created_at));
            case 'actions':
                $id = intval($item->id);
                $nonce = wp_create_nonce('nh_view_' . $id); 

                $actions = [];
                $actions['view'] = '<a href="#" class="nh-open-modal" data-id="'.(int)$item->id.'" data-nonce="'.wp_create_nonce('nh_view_'.$item->id).'" title="'.esc_attr__('View','notification-hub').'"><span class="dashicons dashicons-visibility"></span></a>';
                $actions['mark_read'] = '<a href="'.wp_nonce_url(admin_url('admin-post.php?action=nh_mark_read&id='.(int)$id), 'nh_mark_read').'">'.__('Mark as Read','notification-hub').'</a>';

                $ctx = is_string($item->context) ? json_decode($item->context, true) : [];
                $links = [];
                if (!empty($ctx['order_id'])) {
                    $links[] = '<a href="' . esc_url(admin_url('post.php?post='.(int)$ctx['order_id'].'&action=edit')) . '">'.__('Open Order','notification-hub').'</a>';
                }
                if (!empty($ctx['comment_id'])) {
                    $links[] = '<a href="' . esc_url(admin_url('comment.php?action=editcomment&c='.(int)$ctx['comment_id'])) . '">'.__('Open Comment','notification-hub').'</a>';
                }
                if (!empty($ctx['post_id'])) {
                    $links[] = '<a href="' . esc_url(admin_url('post.php?post='.(int)$ctx['post_id'].'&action=edit')) . '">'.__('Open Post','notification-hub').'</a>';
                }
                if (!empty($ctx['cf7_form_id'])) {
                    $links[] = '<a href="' . esc_url(admin_url('admin.php?page=wpcf7&post='.(int)$ctx['cf7_form_id'].'&action=edit')) . '">'.__('Open Form','notification-hub').'</a>';
                }

                $context_links = $links ? implode(' | ', $links) : '<span class="dashicons dashicons-info"></span> '.__('No context','notification-hub');
                return implode(' | ', $actions) . '<br>' . $context_links;
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
                case 'nh_bulk_mark_read':
                    $wpdb->update($table, ['read_at' => current_time('mysql')], ['id' => $id], ['%s'], ['%d']);
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

add_action('wp_ajax_nh_mark_all_read', function(){
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Forbidden'], 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $now = current_time('mysql');

    // Mark all unread as read
    $updated = $wpdb->query(
        $wpdb->prepare("UPDATE {$table} SET read_at = %s WHERE read_at IS NULL", $now)
    );

    // Update last seen timestamp
    update_option('nh_badge_last_seen_at', $now);

    wp_send_json_success(['updated' => (int)$updated]);
});
