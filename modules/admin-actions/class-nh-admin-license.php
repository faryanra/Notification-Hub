<?php
/**
 * NH_Admin_License
 *
 * License management (save/revoke).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Admin_License {

    /**
     * Register license action handlers.
     *
     * @since 1.6.2
     * @return void
     */
    public static function init(): void {
        add_action('admin_post_nh_save_license', [__CLASS__, 'save']);
        add_action('admin_post_nh_license_revoke', [__CLASS__, 'revoke']);
    }

    /**
     * Save license key.
     *
     * @since 1.6.2
     * @return void
     */
    public static function save(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_save_license');

        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';

        if (class_exists('NH_License')) {
            NH_License::save_key($key);

            if (method_exists('NH_License', 'set_valid')) {
                NH_License::set_valid($key !== '');
            }
        }

        // Align with templates/settings.php query params.
        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_saved=1'));
        exit;
    }

    /**
     * Revoke license key.
     *
     * @since 1.6.2
     * @return void
     */
    public static function revoke(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_license_revoke');

        if (class_exists('NH_License') && method_exists('NH_License', 'revoke')) {
            NH_License::revoke();
        }

        // Align with templates/settings.php query params.
        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_revoked=1'));
        exit;
    }
}