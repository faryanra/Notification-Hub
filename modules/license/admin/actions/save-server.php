<?php
/**
 * Admin action: save license server URL.
 *
 * Legacy handler for pre-1.7 UI. Kept for backward compatibility.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Action_Save_Server {

    /**
     * Handle request.
     *
     * @since 1.7.2
     * @return void
     */
    public function handle(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_save_license_server');

        $url = isset($_POST['nh_license_server_url']) ? esc_url_raw(wp_unslash($_POST['nh_license_server_url'])) : '';

        // Save using Option Store when possible.
        if (!class_exists('NH_License_Option_Store')) {
            $store = NH_PLUGIN_DIR . 'modules/license/storage/option-store.php';
            if (file_exists($store)) {
                require_once $store;
            }
        }

        if (class_exists('NH_License_Option_Store') && class_exists('NH_License') && defined('NH_License::OPT_SERVER_URL')) {
            NH_License_Option_Store::set(NH_License::OPT_SERVER_URL, $url, false);
        } elseif (class_exists('NH_License') && defined('NH_License::OPT_SERVER_URL')) {
            update_option(NH_License::OPT_SERVER_URL, $url, false);
        }

        // If server URL changes, reset state so next load re-checks.
        if (class_exists('NH_License') && method_exists('NH_License', 'reset_state')) {
            NH_License::reset_state();
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_server_saved=1'));
        exit;
    }
}
