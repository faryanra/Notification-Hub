<?php
/**
 * Admin action: save license key.
 *
 * Legacy handler for pre-1.7 UI. Kept for backward compatibility.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Action_Save_Key {

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
        check_admin_referer('nh_save_license');

        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';

        if (class_exists('NH_License')) {
            NH_License::save_key($key);

            // Legacy behavior for 1.6.x: mark as valid when non-empty.
            if (method_exists('NH_License', 'set_valid')) {
                NH_License::set_valid($key !== '');
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_saved=1'));
        exit;
    }
}
