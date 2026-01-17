<?php
/**
 * NH_License
 *
 * Stores and retrieves Pro license state.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_License {

    /**
     * Option key: license string.
     *
     * @since 1.6.2
     */
    public const OPT_KEY = 'nh_license_key';

    /**
     * Option key: license validity flag.
     *
     * @since 1.6.2
     */
    public const OPT_VALID = 'nh_license_valid';

    /**
     * Return true if Pro features are allowed.
     *
     * @since 1.6.2
     * @return bool
     */
    public static function is_pro(): bool {
        return (bool) get_option(self::OPT_VALID, false);
    }

    /**
     * Get the currently saved license key.
     *
     * @since 1.6.2
     * @return string
     */
    public static function get_key(): string {
        $key = get_option(self::OPT_KEY, '');
        return is_string($key) ? $key : '';
    }

    /**
     * Save a new license key and reset validation to false.
     *
     * @since 1.6.2
     * @param string $key License key.
     * @return void
     */
    public static function save_key(string $key): void {
        update_option(self::OPT_KEY, sanitize_text_field($key));
        update_option(self::OPT_VALID, false);
    }

    /**
     * Manually mark license as valid/invalid.
     *
     * @since 1.6.2
     * @param bool $state Valid state.
     * @return void
     */
    public static function set_valid(bool $state): void {
        update_option(self::OPT_VALID, (bool) $state);
    }

    /**
     * Revoke the license completely.
     *
     * @since 1.6.2
     * @return void
     */
    public static function revoke(): void {
        delete_option(self::OPT_KEY);
        delete_option(self::OPT_VALID);
    }
}