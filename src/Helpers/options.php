<?php
namespace NotificationHub\Helpers;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Options helper.
 *
 * @since 1.0.0
 */
final class Options {
    /**
     * @template T
     * @param string $key
     * @param T $default
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        return get_option($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $autoload
     */
    public static function set(string $key, $value, bool $autoload = false): bool {
        return update_option($key, $value, $autoload);
    }

    public static function delete(string $key): bool {
        return delete_option($key);
    }
}

