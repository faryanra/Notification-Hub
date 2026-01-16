<?php
/**
 * License Management
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Admin_License {

    /**
     * Register license action handlers
     */
    public static function init() {
        add_action('admin_post_nh_save_license',   [__CLASS__, 'save']);
        add_action('admin_post_nh_license_revoke', [__CLASS__, 'revoke']);
    }

    /**
     * Save license key
     */
    public static function save() {
        NH_Security::ensure_cap();
        check_admin_referer('nh_save_license');

        $key = sanitize_text_field($_POST['nh_license_key'] ?? '');
        NH_License::save_key($key);
        NH_License::set_valid(!empty($key));

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&license_saved=1'));
        exit;
    }

    /**
     * Revoke license key
     */
    public static function revoke() {
        NH_Security::ensure_cap();
        check_admin_referer('nh_license_revoke');

        NH_License::revoke();
        wp_safe_redirect(admin_url('admin.php?page=nh_settings&license_revoked=1'));
        exit;
    }
}
