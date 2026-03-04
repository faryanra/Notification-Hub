<?php

namespace NotificationHub\Helpers;

/**
 * Date helper.
 *
 * @since 1.7.2
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
