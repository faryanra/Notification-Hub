<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if current execution is on a WP-Cron run.
 *
 * @since 1.7.2
 */
final class IsCron implements Conditional {
    public function passes(): bool {
        return function_exists('wp_doing_cron') ? wp_doing_cron() : (defined('DOING_CRON') && DOING_CRON);
    }
}
