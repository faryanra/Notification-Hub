<?php
namespace NotificationHub\Integrations\Cron;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Registers a daily cleanup cron to remove old notifications.
 *
 * @since 1.0.0
 */
final class CleanupOldNotifications implements Integration {
    public const HOOK = 'nh_cleanup_old_notifications';

    public function register(Loader $loader): void {
        // Schedule on init so WP cron is available.
        $loader->addAction('init', [$this, 'schedule']);
        $loader->addAction(self::HOOK, [$this, 'run']);
    }

    public function schedule(): void {
        if (wp_next_scheduled(self::HOOK)) {
            return;
        }

        // Once per day; can be adjusted later by settings.
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::HOOK);
    }

    public function run(): void {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_notifications';

        // Default retention: 30 days.
        $days      = (int) apply_filters('nh_cleanup_retention_days', 30);
        $threshold = gmdate('Y-m-d H:i:s', time() - max(1, $days) * DAY_IN_SECONDS);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE created_at < %s", $threshold));
    }
}

