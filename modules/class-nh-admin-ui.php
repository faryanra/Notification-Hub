<?php
// NH v1.3.0 — Admin Menus & Settings Wrapper

if (!defined('ABSPATH')) exit;

class NH_Admin_UI {
    protected $r;

    public function __construct($registry = null) { 
        $this->r = $registry;
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
    }

    public function register_menus() {
        // main menu
        add_menu_page(
            __('Notification Hub','notification-hub'),
            __('Notification Hub','notification-hub'),
            'manage_options',
            'nh-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-megaphone',
            58
        );

        // submenus
        add_submenu_page('nh-dashboard','Dashboard','Dashboard','manage_options','nh-dashboard',[$this,'render_dashboard']);
        add_submenu_page('nh-dashboard','Hooks','Hooks','manage_options','nh-hooks',[$this,'render_hooks']);
        add_submenu_page('nh-dashboard','Settings','Settings','manage_options','nh-settings',[$this,'render_settings']);
    }

    public function register_settings() {
        register_setting('nh_settings', 'nh_retention_days', ['type'=>'integer','default'=>90]);
        register_setting('nh_settings', 'nh_email_to', ['type'=>'string','default'=>get_option('admin_email')]);
        register_setting('nh_settings', 'nh_telegram_bot_token', ['type'=>'string','default'=>'']);
        register_setting('nh_settings', 'nh_telegram_chat_id', ['type'=>'string','default'=>'']);
        register_setting('nh_settings', 'nh_slack_webhook', ['type'=>'string','default'=>'']);
        register_setting('nh_settings', 'nh_license_key', ['type'=>'string','default'=>'']);
    }

    public function enqueue_admin($hook) {
        if (strpos($hook, 'nh-') === false) return;
        wp_enqueue_style('nh-admin', NH_PLUGIN_URL . 'assets/css/admin.css', [], NH_VERSION);
    }

    public function render_dashboard() {
        if (!current_user_can('manage_options')) wp_die(__('No access','notification-hub'));
        // ✅ Use NH_Dashboard class
        if (class_exists('NH_Dashboard')) {
            $dashboard = new NH_Dashboard($this->r);
            $dashboard->render();
        } else {
            echo '<div class="wrap"><p>Dashboard class missing.</p></div>';
        }
    }

    public function render_hooks() {
        $file = NH_PLUGIN_DIR . 'templates/hooks.php';
        if (file_exists($file)) include $file;
        else echo '<div class="wrap"><h1>Hooks</h1><p>Template not found.</p></div>';
    }

    public function render_settings() {
        $file = NH_PLUGIN_DIR . 'templates/settings.php';
        if (file_exists($file)) include $file;
        else echo '<div class="wrap"><h1>Settings</h1><p>Template not found.</p></div>';
    }
}
