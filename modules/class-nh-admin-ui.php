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
    }

    public function register_menus() {
        // Top-level menu
        add_menu_page(
            __('Notification Hub', 'notification-hub'),
            __('Notification Hub', 'notification-hub'),
            'manage_options',
            'nh-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-bell',
            58
        );

        // Submenu items
        add_submenu_page('nh-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'nh-dashboard', [$this, 'render_dashboard']);
        add_submenu_page('nh-dashboard', 'Hooks', 'Hooks', 'manage_options', 'nh-hooks', [$this, 'render_hooks']);
        
        // ✅ Slug fixed: nh-settings → nh_settings
        add_submenu_page('nh-dashboard', 'Settings', 'Settings', 'manage_options', 'nh_settings', [$this, 'render_settings']);
    }

    public function register_settings() {
        register_setting('nh_settings', 'nh_retention_days', ['type' => 'integer', 'default' => 90]);
        register_setting('nh_settings', 'nh_email_to', ['type' => 'string', 'default' => get_option('admin_email')]);
        register_setting('nh_settings', 'nh_telegram_bot_token', ['type' => 'string', 'default' => '']);
        register_setting('nh_settings', 'nh_telegram_chat_id', ['type' => 'string', 'default' => '']);
        register_setting('nh_settings', 'nh_slack_webhook', ['type' => 'string', 'default' => '']);
        register_setting('nh_settings', 'nh_license_key', ['type' => 'string', 'default' => '']);
    }

    public function enqueue_assets($hook) {
        // Only load styles and scripts on our plugin pages
        if ($hook !== 'toplevel_page_nh-dashboard') return;

        wp_enqueue_style('nh-admin', NH_PLUGIN_URL . 'assets/css/admin.css', [], NH_VERSION);
        wp_enqueue_style('nh-notifications', NH_PLUGIN_URL . 'assets/css/notifications.css', [], NH_VERSION);

        wp_enqueue_script('nh-dashboard', NH_PLUGIN_URL . 'assets/js/dashboard.js', ['jquery'], NH_VERSION, true);
        wp_enqueue_script('nh-admin', NH_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], NH_VERSION, true);

        // ✅ Localize i18n strings for JS alerts
        wp_localize_script('nh-dashboard', 'nh_i18n', [
            'no_ajax'      => __('AJAX URL not available.', 'notification-hub'),
            'load_error'   => __('Failed to load notification.', 'notification-hub'),
            'request_fail' => __('Request failed', 'notification-hub'),
        ]);

        wp_localize_script('nh-admin', 'nhAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
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

        // Load the modal template
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
