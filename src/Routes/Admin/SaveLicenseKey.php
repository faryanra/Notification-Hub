<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: save license key only (legacy compat action).
 *
 * @since 1.7.2
 */
final class SaveLicenseKey {
    public function handle(): void {
        Capabilities::ensureManageOptions();
        check_admin_referer('nh_save_license');

        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';

        if (class_exists('NH_License')) {
            \NH_License::save_key($key);

            if (method_exists('NH_License', 'set_valid')) {
                \NH_License::set_valid($key !== '');
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_saved=1'));
        exit;
    }
}
