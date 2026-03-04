<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Enqueue admin assets.
 *
 * @since 1.7.2
 */
final class AdminAssets implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('admin_enqueue_scripts', [$this, 'enqueue'], 10, 1);
    }

    /**
     * @param string $hook Current admin page hook suffix.
     */
    public function enqueue(string $hook): void {
        $hook         = (string) $hook;
        $is_dashboard = ($hook === 'toplevel_page_nh-dashboard') || (strpos($hook, 'nh-dashboard') !== false);
        $is_hooks     = (strpos($hook, 'nh-hooks') !== false);
        $is_settings  = (strpos($hook, 'nh_settings') !== false);

        if (!$is_dashboard && !$is_hooks && !$is_settings) {
            return;
        }

        // Global admin JS.
        wp_enqueue_script(
            'nh-admin',
            NH_PLUGIN_URL . 'assets/js/admin/global.js',
            ['jquery'],
            NH_VERSION,
            true
        );

        wp_localize_script('nh-admin', 'nhAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('nh_ajax_nonce'),
            'i18n'     => [
                'badge_new' => esc_html__('New', 'notification-hub'),
            ],
        ]);

        // Variables (optional).
        $vars_path = NH_PLUGIN_DIR . 'assets/css/nh-variables.css';
        if (file_exists($vars_path)) {
            wp_enqueue_style('nh-variables', NH_PLUGIN_URL . 'assets/css/nh-variables.css', [], NH_VERSION);
        }
        $style_deps = wp_style_is('nh-variables', 'enqueued') ? ['nh-variables'] : [];

        // Global admin CSS.
        wp_enqueue_style('nh-admin', NH_PLUGIN_URL . 'assets/css/admin/global.css', $style_deps, NH_VERSION);

        if ($is_dashboard) {
            wp_enqueue_style('nh-dashboard', NH_PLUGIN_URL . 'assets/css/admin/dashboard.css', $style_deps, NH_VERSION);

            wp_enqueue_script(
                'nh-dashboard',
                NH_PLUGIN_URL . 'assets/js/admin/dashboard.js',
                ['jquery', 'nh-admin'],
                NH_VERSION,
                true
            );

            wp_localize_script('nh-dashboard', 'nh_i18n', [
                'no_ajax'      => esc_html__('AJAX URL not available.', 'notification-hub'),
                'load_error'   => esc_html__('Failed to load notification.', 'notification-hub'),
                'request_fail' => esc_html__('Request failed.', 'notification-hub'),
            ]);

            $rest_root = trailingslashit(get_rest_url(null, 'nh/v1'));
            wp_localize_script('nh-dashboard', 'nhREST', [
                'root'       => esc_url_raw($rest_root),
                'nonce'      => wp_create_nonce('wp_rest'),
                'server_now' => current_time('mysql'),
            ]);
        }

        if ($is_settings) {
            wp_enqueue_style('nh-settings', NH_PLUGIN_URL . 'assets/css/admin/settings.css', $style_deps, NH_VERSION . '-settings', 'all');

            wp_enqueue_script(
                'nh-settings',
                NH_PLUGIN_URL . 'assets/js/admin/settings.js',
                ['jquery', 'nh-admin'],
                NH_VERSION . '-settings',
                true
            );
        }

        if ($is_hooks) {
            wp_enqueue_style('nh-hooks', NH_PLUGIN_URL . 'assets/css/admin/hooks.css', $style_deps, NH_VERSION . '-hooks', 'all');

            wp_enqueue_script(
                'nh-hooks',
                NH_PLUGIN_URL . 'assets/js/admin/hooks.js',
                ['jquery', 'nh-admin'],
                NH_VERSION . '-hooks',
                true
            );
        }
    }
}
