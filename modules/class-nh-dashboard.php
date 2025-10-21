<?php
// NH v1.2.0 — Simple dashboard & admin bar badge

if (!defined('ABSPATH')) exit;

class NH_Dashboard {
    protected $r;

    public function __construct($registry){ $this->r = $registry; }

    public function hooks() {
        add_action('admin_bar_menu', [$this,'badge'], 100);
        add_action('admin_enqueue_scripts', [$this,'assets']);
    }

    public function badge($bar) {
        // NH v1.2.0 — Show unread count (quick & light)
        $db = $this->r->get_svc('db');
        $unread = count($db->get_list(['status'=>0],1,5));
        $bar->add_node([
            'id' => 'nh_unread',
            'title' => 'NH: '.$unread,
            'href' => admin_url('admin.php?page=nh_dashboard'),
            'meta' => ['title'=>'Notification Hub']
        ]);
    }

    public function assets() {
        // NH v1.2.0 — Enqueue dashboard assets
        wp_enqueue_style('nh-notifications', NH_PLUGIN_URL.'assets/css/notifications.css', [], NH_VERSION);
        wp_enqueue_script('nh-dashboard', NH_PLUGIN_URL.'assets/js/dashboard.js', ['jquery'], NH_VERSION, true);
    }
}
