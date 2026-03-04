<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: save license server URL (legacy compat action).
 *
 * @since 1.7.2
 */
final class SaveLicenseServer {
    public function handle(): void {
        Capabilities::ensureManageOptions();
        check_admin_referer('nh_save_license_server');

        $url = isset($_POST['nh_license_server_url']) ? esc_url_raw(wp_unslash($_POST['nh_license_server_url'])) : '';

        if (class_exists('NH_License') && defined('NH_License::OPT_SERVER_URL')) {
            update_option(\NH_License::OPT_SERVER_URL, $url, false);

            if (method_exists('NH_License', 'reset_state')) {
                \NH_License::reset_state();
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_server_saved=1'));
        exit;
    }
}
