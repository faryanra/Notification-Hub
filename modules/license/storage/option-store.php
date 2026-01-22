<?php
/**
 * Option store for License module.
 *
 * Centralizes all get_option/update_option/delete_option calls for license state.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Option_Store {

    /**
     * Get option value.
     *
     * @since 1.7.2
     * @param string $key Option name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        return get_option($key, $default);
    }

    /**
     * Set option value.
     *
     * @since 1.7.2
     * @param string $key Option name.
     * @param mixed  $value Option value.
     * @param bool   $autoload Autoload flag.
     * @return bool
     */
    public static function set(string $key, $value, bool $autoload = false): bool {
        return update_option($key, $value, $autoload);
    }

    /**
     * Delete option.
     *
     * @since 1.7.2
     * @param string $key Option name.
     * @return bool
     */
    public static function delete(string $key): bool {
        return delete_option($key);
    }
}
