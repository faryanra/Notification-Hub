<?php
/**
 * Admin action: save license bundle.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Action_Save_Bundle {

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
        check_admin_referer('nh_save_license_bundle');

        $url = isset($_POST['nh_license_server_url']) ? esc_url_raw(wp_unslash($_POST['nh_license_server_url'])) : '';
        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';
        $key = strtoupper(trim((string) $key));

        // IMPORTANT: Use the same tab slug used by the UI (premium).
        $redirect_base = 'admin.php?page=nh_settings&tab=premium';

        // Validate only when user attempts to submit a key.
        if ($key !== '' && !preg_match('/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key)) {
            wp_safe_redirect(admin_url($redirect_base . '&nh_license_error=invalid_key'));
            exit;
        }

        if (class_exists('NH_License')) {
            // Save server url (optional).
            if (defined('NH_License::OPT_SERVER_URL')) {
                update_option(NH_License::OPT_SERVER_URL, $url, false);
            }

            // Save key (optional).
            if (method_exists('NH_License', 'save_key')) {
                NH_License::save_key($key);
            }

            // Reset state so it re-checks immediately on next load.
            if (method_exists('NH_License', 'reset_state')) {
                NH_License::reset_state();
            }

            // Try to refresh state now (best effort).
            if (method_exists('NH_License', 'maybe_refresh')) {
                NH_License::maybe_refresh();
            }
        }

        wp_safe_redirect(admin_url($redirect_base . '&nh_license_saved=1'));
        exit;
    }
}
