<?php
// License Manager
// Stores/reads license state. In future can call remote to verify key.

if (!defined('ABSPATH')) exit;

class NH_License {

    /**
     * Return true if Pro features are allowed.
     * During development (WP_DEBUG), always allow all channels.
     */
    public static function is_pro(): bool {
        // Development override: allow Pro in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return true;
        }

        $valid = get_option('nh_license_valid', false);
        return (bool) $valid;
    }

    /**
     * Get the currently saved license key (masked usage in UI).
     */
    public static function get_key(): string {
        $key = get_option('nh_license_key', '');
        return is_string($key) ? $key : '';
    }

    /**
     * Store a new license key and mark as not-yet-validated.
     * We'll mark nh_license_valid=false here.
     * After this, NH_Admin_Actions can optionally attempt remote validation
     * and then set nh_license_valid=true if server approves.
     */
    public static function save_key(string $key) {
        update_option('nh_license_key', sanitize_text_field($key));
        update_option('nh_license_valid', false); // default locked until validated
    }

    /**
     * Set final validation result.
     * This should only be called after capability+nonce checks.
     */
    public static function set_valid(bool $is_valid) {
        update_option('nh_license_valid', $is_valid ? 1 : 0);
    }
}
