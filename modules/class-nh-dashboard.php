<?php
// NH v1.3.0 — Dashboard (refined filtering + fixed bulk refresh + UX-consistent)

if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/* ============================================================
   NH_Dashboard — Dashboard Renderer Only
============================================================ */
class NH_Dashboard {
    protected $r;

    public function __construct($registry) {
        $this->r = $registry;
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_bar_menu', [$this, 'badge'], 100);
    }

    public function assets($hook = '') {
        if (strpos($hook, 'nh-dashboard') === false) return;

        wp_enqueue_style('nh-notifications', NH_PLUGIN_URL . 'assets/css/notifications.css', [], NH_VERSION);
        wp_enqueue_script('nh-dashboard', NH_PLUGIN_URL . 'assets/js/dashboard.js', ['jquery'], NH_VERSION, true);
        wp_localize_script('nh-dashboard', 'nhAdmin', ['ajax_url' => admin_url('admin-ajax.php')]);
    }

    public function badge($bar) {
        global $wpdb;
        $unread = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nh_notifications WHERE status=0");
        $bar->add_node([
            'id'    => 'nh_unread',
            'title' => 'NH: ' . $unread,
            'href'  => admin_url('admin.php?page=nh-dashboard'),
            'meta'  => ['title' => __('Notification Hub', 'notification-hub')]
        ]);
    }

    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'notification-hub'));
        }

        echo '<div class="wrap"><h1>' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1>';

        $status_filter = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
        $table = new NH_Dashboard_Table($status_filter);
        $table->prepare_items();

        // ✅ Header wrapper (tabs + search box in one line)
        echo '<div class="nh-dashboard-header">';

        // Tabs (All | Active | Archived)
        $views = $table->get_views();
        if (!empty($views)) {
            echo '<ul class="subsubsub">';
            $last = array_key_last($views);
            foreach ($views as $key => $v) {
                echo '<li>' . $v . ($key !== $last ? ' | ' : '') . '</li>';
            }
            echo '</ul>';
        }

        // Search box aligned right
        echo '<div class="search-box">';
        $table->search_box(__('Search Notifications', 'notification-hub'), 'nh-search');
        echo '</div>';

        echo '</div>'; // end .nh-dashboard-header

        // Main table form
        echo '<form method="post">';
        wp_nonce_field('bulk-notifications');
        echo '<input type="hidden" name="page" value="nh-dashboard" />';
        $table->display();
        echo '</form>';

        echo '</div>'; // end .wrap
    }

}

/* ============================================================
   NH_Dashboard_Table
============================================================ */
class NH_Dashboard_Table extends WP_List_Table {
    private $per_page = 20;
    private $status_filter;
    private $counts = ['all'=>0,'0'=>0,'1'=>0];

    public function __construct($filter='all') {
        parent::__construct(['singular'=>'notification','plural'=>'notifications']);
        $this->status_filter = $filter;
        $this->prime_counts();
    }

    private function prime_counts() {
        global $wpdb;
        $t = $wpdb->prefix . 'nh_notifications';
        $this->counts['all'] = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t WHERE status=0"); // ✅ فقط Active
        $this->counts['0']   = $this->counts['all'];
        $this->counts['1']   = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t WHERE status=1");
    }

    protected function get_views() {
        $base = admin_url('admin.php?page=nh-dashboard');
        return [
            'all'      => '<a href="'.$base.'" '.($this->status_filter==='all'?'class="current"':'').'>All <span class="count">('.$this->counts['all'].')</span></a>',
            'active'   => '<a href="'.$base.'&filter_status=0" '.($this->status_filter==='0'?'class="current"':'').'>Active <span class="count">('.$this->counts['0'].')</span></a>',
            'archived' => '<a href="'.$base.'&filter_status=1" '.($this->status_filter==='1'?'class="current"':'').'>Archived <span class="count">('.$this->counts['1'].')</span></a>',
        ];
    }

    protected function get_bulk_actions() {
        // ✅ Bulk Unarchive در حالت Archived اضافه شده
        return $this->status_filter==='1'
            ? ['unarchive'=>__('Unarchive','notification-hub'),'delete'=>__('Delete','notification-hub')]
            : ['archive'=>__('Archive','notification-hub'),'delete'=>__('Delete','notification-hub')];
    }

    public function get_columns() {
        return [
            'cb'=>'<input type="checkbox" />',
            'id'=>'ID',
            'source'=>'Source',
            'type'=>'Type',
            'title'=>'Title',
            'message'=>'Message',
            'status'=>'Status',
            'created_at'=>'Created',
            'actions'=>'Actions'
        ];
    }

    protected function column_cb($item) {
        return '<input type="checkbox" name="ids[]" value="'.intval($item->id).'" />';
    }

    protected function column_default($item, $col) {
        switch ($col) {
            case 'status':
                return $item->status==0?'<span class="nh-badge nh-badge--ok">Active</span>':'<span class="nh-badge nh-badge--archived">Archived</span>';
            case 'message':
                return esc_html(wp_strip_all_tags(mb_strimwidth($item->message,0,100,'…')));
            case 'created_at':
                return esc_html(mysql2date('Y-m-d H:i',$item->created_at));
            case 'actions':
                $id = intval($item->id);
                $view = wp_create_nonce('nh_view_'.$id);
                $toggle = wp_create_nonce('nh_toggle_'.$id);
                $do = $item->status==1?'unarchive':'archive';
                $view_btn = '<a href="#" class="button nh-view" data-id="'.$id.'" data-nonce="'.$view.'">View</a>';
                $toggle_url = add_query_arg(['action'=>'nh_toggle_archive','id'=>$id,'do'=>$do,'_wpnonce'=>$toggle],admin_url('admin-post.php'));
                $toggle_btn = '<a href="'.esc_url($toggle_url).'" class="button-link">'.ucfirst($do).'</a>';
                $del_url = wp_nonce_url(admin_url('admin-post.php?action=nh_delete_notification&id='.$id),'nh_delete_'.$id);
                return $view_btn.' '.$toggle_btn.' <a href="'.$del_url.'" class="button-link-delete">Delete</a>';
            default:
                return esc_html($item->$col);
        }
    }

    public function prepare_items() {
        $this->process_bulk_action();
        $this->prime_counts();
        global $wpdb;
        $t = $wpdb->prefix . 'nh_notifications';
        $order = (isset($_GET['order']) && $_GET['order']=='asc')?'ASC':'DESC';
        $orderby = isset($_GET['orderby'])?sanitize_key($_GET['orderby']):'created_at';
        $search = isset($_REQUEST['s'])?trim(wp_unslash($_REQUEST['s'])):'';
        $where = 'WHERE 1=1'; $p=[];

        // ✅ اصلاح فیلتر: "All" فقط Active را نشان می‌دهد
        if ($this->status_filter === '1') {
            $where .= " AND status = 1";
        } else {
            $where .= " AND status = 0";
        }

        if ($search!=='') {
            $where.=" AND (source LIKE %s OR title LIKE %s OR message LIKE %s)";
            $like='%'.$wpdb->esc_like($search).'%'; array_push($p,$like,$like,$like);
        }

        $count_sql="SELECT COUNT(*) FROM $t $where";
        $total = empty($p)?$wpdb->get_var($count_sql):$wpdb->get_var($wpdb->prepare($count_sql,$p));
        $paged=$this->get_pagenum(); $offset=($paged-1)*$this->per_page;
        $sql="SELECT * FROM $t $where ORDER BY $orderby $order LIMIT %d OFFSET %d";
        array_push($p,$this->per_page,$offset);
        $this->items = $wpdb->get_results($wpdb->prepare($sql,$p));

        $this->_column_headers = [$this->get_columns(),[],[]];
        $this->set_pagination_args(['total_items'=>$total,'per_page'=>$this->per_page]);
    }

    public function process_bulk_action() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // بررسی nonce
        if (!empty($_POST['_wpnonce']) && !wp_verify_nonce($_POST['_wpnonce'], 'bulk-notifications')) {
            return;
        }

        $action = $this->current_action();
        if (empty($action)) return;

        $ids = isset($_POST['ids']) ? array_map('intval', (array)$_POST['ids']) : [];
        if (empty($ids)) return;

        foreach ($ids as $id) {
            if ($action === 'delete') {
                $wpdb->delete($table, ['id' => $id], ['%d']);
            } elseif ($action === 'archive') {
                $wpdb->update($table, ['status' => 1], ['id' => $id], ['%d'], ['%d']);
            } elseif ($action === 'unarchive') {
                $wpdb->update($table, ['status' => 0], ['id' => $id], ['%d'], ['%d']);
            }
        }
    }
}

/* -----------------------------------------------------------
   AJAX + AdminPost Handlers
------------------------------------------------------------*/
add_action('wp_ajax_nh_view_notification', function(){
    if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'forbidden'],403);
    $id = intval($_REQUEST['id']??0);
    $nonce = $_REQUEST['_wpnonce']??($_REQUEST['nonce']??'');
    if(!$id || !wp_verify_nonce($nonce,'nh_view_'.$id)) wp_send_json_error(['message'=>'bad nonce'],400);
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nh_notifications WHERE id=%d",$id));
    if(!$row) wp_send_json_error(['message'=>'not found'],404);
    wp_send_json_success(['title'=>$row->title,'message'=>$row->message,'source'=>$row->source,'status'=>$row->status]);
});

add_action('admin_post_nh_delete_notification', function(){
    if(!current_user_can('manage_options')) wp_die('Not allowed');
    $id=intval($_GET['id']??0); check_admin_referer('nh_delete_'.$id);
    global $wpdb; $wpdb->delete("{$wpdb->prefix}nh_notifications",['id'=>$id],['%d']);
    wp_safe_redirect(admin_url('admin.php?page=nh-dashboard'));
    exit;
});

add_action('admin_post_nh_toggle_archive', function(){
    if(!current_user_can('manage_options')) wp_die('Not allowed');
    $id=intval($_GET['id']??0); $do=sanitize_key($_GET['do']??'');
    check_admin_referer('nh_toggle_'.$id);
    if(!$id || !in_array($do,['archive','unarchive'],true)) wp_safe_redirect(admin_url('admin.php?page=nh-dashboard'));
    global $wpdb; $wpdb->update("{$wpdb->prefix}nh_notifications",['status'=>$do==='archive'?1:0],['id'=>$id],['%d'],['%d']);
    wp_safe_redirect(admin_url('admin.php?page=nh-dashboard'));
    exit;
});
