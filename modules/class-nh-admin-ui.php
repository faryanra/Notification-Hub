<?php
/**
 * NH_Admin_UI
 *
 * Admin UI and menu registration. Handles admin pages routing and enqueueing assets.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) exit;

class NH_Admin_UI {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var mixed
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry = null) {
        $this->r = $registry;

        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_badge'], 100);
    }

    /**
     * Register admin menus.
     *
     * @since 1.6.2
     * @return void
     */
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

        add_submenu_page(
            'nh-dashboard',
            __('Dashboard', 'notification-hub'),
            __('Dashboard', 'notification-hub'),
            'manage_options',
            'nh-dashboard',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'nh-dashboard',
            __('Hooks', 'notification-hub'),
            __('Hooks', 'notification-hub'),
            'manage_options',
            'nh-hooks',
            [$this, 'render_hooks']
        );

        add_submenu_page(
            'nh-dashboard',
            __('Settings', 'notification-hub'),
            __('Settings', 'notification-hub'),
            'manage_options',
            'nh_settings',
            [$this, 'render_settings']
        );
    }

    /**
     * Register settings.
     *
     * @since 1.6.2
     * @return void
     */
    public function register_settings() {
        // General
        register_setting('nh_settings', 'nh_retention_days');
        register_setting('nh_settings', 'nh_email_to');
        register_setting('nh_settings', 'nh_keep_data_on_uninstall');

        // Pro
        register_setting('nh_settings', 'nh_telegram_bot_token');
        register_setting('nh_settings', 'nh_telegram_chat_id');
        register_setting('nh_settings', 'nh_slack_webhook');
    }

    /**
     * Enqueue admin assets.
     *
     * - Always loads `nh-admin` (admin bar badge refresh, global confirm handler, etc.)
     * - Loads page-specific CSS/JS for Dashboard, Hooks, and Settings pages.
     *
     * @since 1.6.2
     * @param string $hook Current admin page hook suffix.
     * @return void
     */
    public function enqueue_assets($hook) {
        /**
         * Base admin JS (global for NH pages; safe to load on all admin screens)
         */
        wp_enqueue_script(
            'nh-admin',
            NH_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            NH_VERSION,
            true
        );

        // Keep `nhAdmin` for AJAX usage across admin scripts.
        wp_localize_script('nh-admin', 'nhAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('nh_ajax_nonce'),
            // Optional i18n container for admin.js (we will fill keys at the end).
            'i18n'     => [
                'badge_new' => __('New', 'notification-hub'),
            ],
        ]);

        /**
         * Optional CSS variables (only if file exists)
         * This avoids 404 noise and keeps it truly optional.
         */
        $vars_path = NH_PLUGIN_DIR . 'assets/css/nh-variables.css';
        if (file_exists($vars_path)) {
            wp_enqueue_style('nh-variables', NH_PLUGIN_URL . 'assets/css/nh-variables.css', [], NH_VERSION);
        }

        /**
         * Detect current NH admin page
         */
        $is_dashboard = ($hook === 'toplevel_page_nh-dashboard') || (strpos((string) $hook, 'nh-dashboard') !== false);
        $is_hooks     = (strpos((string) $hook, 'nh-hooks') !== false);
        $is_settings  = (strpos((string) $hook, 'nh_settings') !== false);

        if (!$is_dashboard && !$is_hooks && !$is_settings) {
            return;
        }

        /**
         * Shared admin styles (Hooks + Settings + Dashboard layout)
         */
        $style_deps = wp_style_is('nh-variables', 'enqueued') ? ['nh-variables'] : [];
        wp_enqueue_style('nh-admin', NH_PLUGIN_URL . 'assets/css/admin.css', $style_deps, NH_VERSION);

        /**
         * Dashboard assets
         */
        if ($is_dashboard) {
            $notif_deps = wp_style_is('nh-variables', 'enqueued') ? ['nh-variables'] : [];
            wp_enqueue_style('nh-notifications', NH_PLUGIN_URL . 'assets/css/notifications.css', $notif_deps, NH_VERSION);

            wp_enqueue_script(
                'nh-dashboard',
                NH_PLUGIN_URL . 'assets/js/dashboard.js',
                ['jquery'],
                NH_VERSION,
                true
            );

            // Minimal JS translations (we will expand later).
            wp_localize_script('nh-dashboard', 'nh_i18n', [
                'no_ajax'      => __('AJAX URL not available.', 'notification-hub'),
                'load_error'   => __('Failed to load notification.', 'notification-hub'),
                'request_fail' => __('Request failed.', 'notification-hub'),
            ]);

            // REST config (uses WP REST root reliably)
            $rest_root = trailingslashit(get_rest_url(null, 'nh/v1'));
            wp_localize_script('nh-dashboard', 'nhREST', [
                'root'       => esc_url_raw($rest_root),
                'nonce'      => wp_create_nonce('wp_rest'),
                'server_now' => current_time('mysql'),
            ]);
        }

        /**
         * Settings assets (replaces inline <script> in templates/settings.php)
         */
        if ($is_settings) {
            wp_enqueue_script(
                'nh-settings',
                NH_PLUGIN_URL . 'assets/js/settings.js',
                [],
                NH_VERSION,
                true
            );
        }

        /**
         * Hooks page currently has no dedicated JS file.
         * (Delete confirm is handled globally via admin.js)
         */
    }

    /**
     * Add unread badge to admin bar.
     *
     * @since 1.6.2
     * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
     * @return void
     */
    public function add_admin_bar_badge($wp_admin_bar) {
        if (!current_user_can('manage_options')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // Only count active + unread
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count_new = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status IN (0,3) AND read_at IS NULL");

        $title  = '<span class="ab-icon dashicons dashicons-bell"></span>';
        $title .= '<span class="ab-label"> ' . (int) $count_new . ' ' . esc_html__('New', 'notification-hub') . '</span>';

        $wp_admin_bar->add_node([
            'id'    => 'nh_unread',
            'title' => $title,
            'href'  => admin_url('admin.php?page=nh-dashboard'),
            'meta'  => ['title' => __('View Notifications', 'notification-hub')],
        ]);
    }

    /**
     * Render dashboard page.
     *
     * @since 1.6.2
     * @return void
     */
    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'notification-hub'));
        }

        if (class_exists('NH_Dashboard')) {
            $dashboard = new NH_Dashboard($this->r);
            $dashboard->render();
        } else {
            echo '<div class="wrap"><p>' . esc_html__('Dashboard class is missing.', 'notification-hub') . '</p></div>';
        }

        $modal_file = NH_PLUGIN_DIR . 'templates/modal-preview.php';
        if (file_exists($modal_file)) {
            include $modal_file;
        }
    }

    /**
     * Render hooks page.
     *
     * @since 1.6.2
     * @return void
     */
    public function render_hooks() {
        $file = NH_PLUGIN_DIR . 'templates/hooks.php';

        if (file_exists($file)) {
            include $file;
            return;
        }

        echo '<div class="wrap"><h1>' . esc_html__('Hooks', 'notification-hub') . '</h1><p>' . esc_html__('Template not found.', 'notification-hub') . '</p></div>';
    }

    /**
     * Render settings page.
     *
     * @since 1.6.2
     * @return void
     */
    public function render_settings() {
        $file = NH_PLUGIN_DIR . 'templates/settings.php';

        if (file_exists($file)) {
            include $file;
            return;
        }

        echo '<div class="wrap"><h1>' . esc_html__('Settings', 'notification-hub') . '</h1><p>' . esc_html__('Template not found.', 'notification-hub') . '</p></div>';
    }
}
