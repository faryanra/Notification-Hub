<?php
/**
 * Admin action: revoke license.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Action_Revoke {

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
        check_admin_referer('nh_license_revoke');

        if (class_exists('NH_License') && method_exists('NH_License', 'revoke')) {
            NH_License::revoke();
        }

        // Keep user on the Premium tab.
        wp_safe_redirect(admin_url('admin.php?page=nh_settings&tab=premium&nh_license_revoked=1'));
        exit;
    }
}
