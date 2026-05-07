<?php
namespace NotificationHub\Helpers;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Date helper.
 *
 * @since 1.0.0
 */
final class Date {
    /**
     * @return string MySQL datetime in WP timezone.
     */
    public static function nowMysql(): string {
        return current_time('mysql');
    }

    /**
     * @param string $mysql
     */
    public static function toTimestamp(string $mysql): int {
        $ts = strtotime($mysql);
        return $ts ? (int) $ts : 0;
    }
}

