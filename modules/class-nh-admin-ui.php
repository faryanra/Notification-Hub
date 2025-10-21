<?php
// NH v1.2.0 — Admin Settings UI (General + Pro Channels)

if (!defined('ABSPATH')) exit;

class NH_Admin_UI {
    protected $r;

    public function __construct($registry){ 
        $this->r = $registry; 
    }

    public function hooks() {
        add_action('admin_menu', [$this,'menu']);
        add_action('admin_init', [$this,'register_settings']);
        add_action('admin_enqueue_scripts', [$this,'assets']);

        // ✅ گام 2 — بعد از ذخیره تنظیمات، تب فعال را در URL حفظ کن
        add_filter('redirect_post_location', [$this, 'preserve_active_tab'], 10, 2);
    }

    public function menu() {
        // NH v1.2.0 — Add main menu + subpages
        add_menu_page(
            __('Notification Hub','notification-hub'),
            __('Notifications','notification-hub'),
            'manage_options',
            'nh_dashboard',
            [$this,'render_dashboard'],
            'dashicons-megaphone',
            56
        );

        add_submenu_page(
            'nh_dashboard',
            __('Settings','notification-hub'),
            __('Settings','notification-hub'),
            'manage_options',
            'nh_settings',
            [$this,'render_settings']
        );
    }

    public function register_settings() {
        // NH v1.2.0 — Settings: retention days, email to, pro fields
        register_setting('nh_settings', 'nh_retention_days', [
            'type' => 'integer',
            'default' => 90
        ]);
        register_setting('nh_settings', 'nh_email_to', [
            'type' => 'string',
            'default' => get_option('admin_email')
        ]);

        // Pro fields for testing (Telegram/Slack/License)
        register_setting('nh_settings', 'nh_telegram_bot_token', [
            'type' => 'string',
            'default' => ''
        ]);
        register_setting('nh_settings', 'nh_telegram_chat_id', [
            'type' => 'string',
            'default' => ''
        ]);
        register_setting('nh_settings', 'nh_slack_webhook', [
            'type' => 'string',
            'default' => ''
        ]);
        register_setting('nh_settings', 'nh_license_key', [
            'type' => 'string',
            'default' => ''
        ]);
    }

    public function assets($hook='') {
        // NH v1.2.0 — Enqueue admin assets
        wp_enqueue_style('nh-admin', NH_PLUGIN_URL.'assets/css/admin.css', [], NH_VERSION);
        wp_enqueue_script('nh-admin', NH_PLUGIN_URL.'assets/js/admin.js', ['jquery'], NH_VERSION, true);
    }

    public function render_dashboard() {
        include NH_PLUGIN_DIR.'templates/dashboard.php';
    }

    public function render_settings() {
        include NH_PLUGIN_DIR . 'templates/setting.php';
    }

    // ✅ گام 2 — افزودن متد جدید برای حفظ تب فعال بعد از ذخیره تنظیمات
    public function preserve_active_tab($location, $post_id) {
        // اگر پارامتر tab در فرم ارسال شده بود، به URL اضافه کن
        if (!empty($_POST['nh_active_tab'])) {
            $tab = sanitize_key($_POST['nh_active_tab']);
            // اطمینان از اینکه تنظیمات ماست نه تنظیمات دیگر افزونه‌ها
            if (strpos($location, 'page=nh_settings') !== false || strpos($location, 'nh_settings') !== false) {
                $location = add_query_arg('tab', $tab, $location);
            }
        }
        return $location;
    }

}
