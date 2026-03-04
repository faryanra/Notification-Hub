<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: revoke license.
 *
 * @since 1.7.2
 */
final class RevokeLicense {
    public function handle(): void {
        Capabilities::ensureManageOptions();
        check_admin_referer('nh_license_revoke');

        if (class_exists('NH_License') && method_exists('NH_License', 'revoke')) {
            \NH_License::revoke();
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&tab=premium&nh_license_revoked=1'));
        exit;
    }
}
