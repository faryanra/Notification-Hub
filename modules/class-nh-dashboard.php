<?php
/**
 * Dashboard Controller
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

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

    /**
     * Load dashboard assets
     */
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

        $this->track_last_seen();
    }

    /**
     * Track user's last visit timestamp
     */
    private function track_last_seen() {
        $uid = get_current_user_id();
        $prev_seen = get_user_meta($uid, 'nh_last_seen_at', true) ?: '1970-01-01 00:00:00';

        wp_localize_script('nh-dashboard', 'nhSeen', [
            'prev' => $prev_seen,
        ]);

        update_user_meta($uid, 'nh_last_seen_at', current_time('mysql'));
    }

    /**
     * Render dashboard page
     */
    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'notification-hub'));
        }

        $status_filter = isset($_GET['filter_status']) 
            ? sanitize_text_field($_GET['filter_status']) 
            : 'all';

        $prev_seen = get_user_meta(get_current_user_id(), 'nh_last_seen_at', true) 
            ?: '1970-01-01 00:00:00';

        $table = new NH_Notifications_Table($status_filter, $prev_seen);
        $table->prepare_items();

        $this->render_header();
        $this->render_views($table->get_views());
        $this->render_table($table);
        $this->render_footer();
    }

    /**
     * Render page header
     */
    private function render_header() {
        echo '<div class="wrap">';
        echo '<div id="nh-table-loader" style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.7);z-index:999;align-items:center;justify-content:center;">';
        echo '<span class="spinner is-active" style="float:none;"></span>';
        echo '</div>';
        echo '<h1 class="wp-heading-inline">' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1>';
        echo '<hr class="wp-header-end">';
    }

    /**
     * Render filter views (tabs)
     */
    private function render_views($views) {
        if (empty($views)) return;

        echo '<ul class="subsubsub">';
        $last = array_key_last($views);
        foreach ($views as $key => $view) {
            echo '<li>' . $view . ($key !== $last ? ' | ' : '') . '</li>';
        }
        echo '</ul>';
    }

    /**
     * Render table
     */
    private function render_table($table) {
        echo '<form method="post">';
        wp_nonce_field('bulk-notifications');
        echo '<input type="hidden" name="page" value="nh-dashboard" />';
        $table->search_box(__('Search Notifications', 'notification-hub'), 'nh-search');
        $table->display();
        echo '</form>';
    }

    /**
     * Render page footer
     */
    private function render_footer() {
        echo '</div>';
    }
}
