<?php
namespace NotificationHub\Initializers;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Register custom cron schedules and schedule NH events.
 *
 * @since 1.0.0
 */
final class CronSchedules implements Integration {
    public const CLEANUP_HOOK = 'nh_cron_cleanup';

    public function register(Loader $loader): void {
        $loader->addFilter('cron_schedules', [$this, 'addSchedules']);
        $loader->addAction('init', [$this, 'maybeSchedule']);
        $loader->addAction(self::CLEANUP_HOOK, [$this, 'runCleanup']);
    }

    /**
     * @param array<string, array{interval:int,display:string}> $schedules
     * @return array<string, array{interval:int,display:string}>
     */
    public function addSchedules(array $schedules): array {
        // Placeholder for future custom intervals.
        return $schedules;
    }

    public function maybeSchedule(): void {
        if (!wp_next_scheduled(self::CLEANUP_HOOK)) {
            wp_schedule_event(time() + 3600, 'daily', self::CLEANUP_HOOK);
        }
    }

    public function runCleanup(): void {
        if (!class_exists(DatabaseMigration::class)) {
            $file = NH_SRC_DIR . 'Initializers/DatabaseMigration.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }

        // Keep dependency light for now; cleanup will be moved to repository/service later.
        if (class_exists('NH_Database')) {
            (new \NH_Database())->cleanup_old();
        }
    }
}

