<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: save license bundle (server URL + key).
 *
 * @since 1.7.2
 */
final class SaveLicenseBundle {
    public function handle(): void {
        Capabilities::ensureManageOptions();
        check_admin_referer('nh_save_license_bundle');

        $url = isset($_POST['nh_license_server_url']) ? esc_url_raw(wp_unslash($_POST['nh_license_server_url'])) : '';
        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';
        $key = strtoupper(trim((string) $key));

        $redirectBase = 'admin.php?page=nh_settings&tab=premium';

        if ($key !== '' && !preg_match('/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key)) {
            wp_safe_redirect(admin_url($redirectBase . '&nh_license_error=invalid_key'));
            exit;
        }

        if (class_exists('NH_License')) {
            if (defined('NH_License::OPT_SERVER_URL')) {
                update_option(\NH_License::OPT_SERVER_URL, $url, false);
            }

            if (method_exists('NH_License', 'save_key')) {
                \NH_License::save_key($key);
            }

            if (method_exists('NH_License', 'reset_state')) {
                \NH_License::reset_state();
            }

            if (method_exists('NH_License', 'maybe_refresh')) {
                \NH_License::maybe_refresh();
            }
        }

        wp_safe_redirect(admin_url($redirectBase . '&nh_license_saved=1'));
        exit;
    }
}
