<?php
// Admin UI and Menu Registration

if (!defined('ABSPATH')) exit;

class NH_Admin_UI {
    protected $r;

    public function __construct($registry = null) {
        $this->r = $registry;
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_bar_menu', [$this, 'nh_global_admin_bar_badge'], 100);
    }

    public function register_menus() {
        add_menu_page(
            __('Notification Hub', 'notification-hub'),
            __('Notification Hub', 'notification-hub'),
            'manage_options',
            'nh-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-bell',
            58
        );

        add_submenu_page('nh-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'nh-dashboard', [$this, 'render_dashboard']);
        add_submenu_page('nh-dashboard', 'Hooks', 'Hooks', 'manage_options', 'nh-hooks', [$this, 'render_hooks']);
        add_submenu_page('nh-dashboard', 'Settings', 'Settings', 'manage_options', 'nh_settings', [$this, 'render_settings']);
    }

    public function register_settings() {
        register_setting('nh_settings', 'nh_retention_days');
        register_setting('nh_settings', 'nh_email_to');
        register_setting('nh_settings', 'nh_keep_data_on_uninstall');
        register_setting('nh_settings', 'nh_telegram_bot_token');
        register_setting('nh_settings', 'nh_telegram_chat_id');
        register_setting('nh_settings', 'nh_slack_webhook');
    }

    public function enqueue_assets($hook) {
        if (is_admin()) {
            wp_enqueue_script('nh-admin', NH_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], NH_VERSION, true);
            wp_localize_script('nh-admin', 'nhAdmin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('nh_ajax_nonce'), // ✅ این خط حیاتی است
            ]);
        }

        if ($hook === 'toplevel_page_nh-dashboard' || strpos($hook, 'nh-dashboard') !== false) {
            wp_enqueue_style('nh-admin', NH_PLUGIN_URL . 'assets/css/admin.css', [], NH_VERSION);
            wp_enqueue_style('nh-notifications', NH_PLUGIN_URL . 'assets/css/notifications.css', [], NH_VERSION);
            wp_enqueue_script('nh-dashboard', NH_PLUGIN_URL . 'assets/js/dashboard.js', ['jquery'], NH_VERSION, true);

            wp_localize_script('nh-dashboard', 'nh_i18n', [
                'no_ajax'      => __('AJAX URL not available.', 'notification-hub'),
                'load_error'   => __('Failed to load notification.', 'notification-hub'),
                'request_fail' => __('Request failed', 'notification-hub'),
            ]);

            $site_url  = get_site_url();
            $rest_root = trailingslashit($site_url) . 'wp-json/nh/v1/';
            wp_localize_script('nh-dashboard', 'nhREST', [
                'root'       => esc_url_raw($rest_root),
                'nonce'      => wp_create_nonce('wp_rest'),
                'server_now' => current_time('mysql'),
            ]);
        }
    }

    public function nh_global_admin_bar_badge($wp_admin_bar) {
        if (!current_user_can('manage_options')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // ✅ Only count active + unread
        $count_new = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 0 AND read_at IS NULL");

        $title  = '<span class="ab-icon dashicons dashicons-bell"></span>';
        $title .= '<span class="ab-label"> ' . $count_new . ' New</span>';

        $wp_admin_bar->add_node([
            'id'    => 'nh_unread',
            'title' => $title,
            'href'  => admin_url('admin.php?page=nh-dashboard'),
            'meta'  => ['title' => __('View Notifications', 'notification-hub')],
        ]);
    }

    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied', 'notification-hub'));
        }

        if (class_exists('NH_Dashboard')) {
            $dashboard = new NH_Dashboard($this->r);
            $dashboard->render();
        } else {
            echo '<div class="wrap"><p>Dashboard class missing.</p></div>';
        }

        $modal_file = NH_PLUGIN_DIR . 'templates/modal-preview.php';
        if (file_exists($modal_file)) {
            include $modal_file;
        }
    }

    public function render_hooks() {
        $file = NH_PLUGIN_DIR . 'templates/hooks.php';
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Hooks</h1><p>Template not found.</p></div>';
        }
    }

    public function render_settings() {
        $file = NH_PLUGIN_DIR . 'templates/settings.php';
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>Settings</h1><p>Template not found.</p></div>';
        }
    }
}
