<?php
// NH v1.5.0 — License Manager (Fixed Validation + Revoke)

if (!defined('ABSPATH')) exit;

class NH_License {

    /**
     * Return true if Pro features are allowed.
     */
    public static function is_pro(): bool {
        $valid = get_option('nh_license_valid', false);
        return (bool) $valid;
    }

    /**
     * Get the currently saved license key (raw or masked in UI).
     */
    public static function get_key(): string {
        $key = get_option('nh_license_key', '');
        return is_string($key) ? $key : '';
    }

    /**
     * Save a new license key and reset validation to false.
     */
    public static function save_key(string $key): void {
        update_option('nh_license_key', sanitize_text_field($key));
        update_option('nh_license_valid', false);
    }

    /**
     * Manually mark license as valid/invalid.
     */
    public static function set_valid(bool $state): void {
        update_option('nh_license_valid', $state);
    }

    /**
     * Revoke the license completely.
     */
    public static function revoke(): void {
        delete_option('nh_license_key');
        delete_option('nh_license_valid');
    }
}
