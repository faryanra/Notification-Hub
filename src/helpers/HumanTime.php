<?php

namespace NotificationHub\Helpers;

/**
 * Human time helper.
 *
 * @since 1.7.2
 */
final class HumanTime {
    /**
     * @param string $mysql
     */
    public static function diff(string $mysql): string {
        $ts = strtotime($mysql);
        if (!$ts) {
            return '';
        }

        return human_time_diff($ts, current_time('timestamp'));
    }
}
