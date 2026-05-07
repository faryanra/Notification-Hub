<?php
namespace NotificationHub\Integrations\Cron;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Services\ServiceFactory;

/**
 * Schedules queue processing on a recurring cron hook.
 *
 * Even if Action Scheduler is available, this provides a WP-Cron fallback.
 *
 * @since 1.0.0
 */
final class ProcessQueue implements Integration {
    public const HOOK = 'nh_process_queue_cron';

    public function register(Loader $loader): void {
        $loader->addAction('init', [$this, 'schedule']);
        $loader->addAction(self::HOOK, [$this, 'run']);
    }

    public function schedule(): void {
        if (wp_next_scheduled(self::HOOK)) {
            return;
        }

        // Every 5 minutes; uses built-in 'twicedaily'/'hourly' only by default, so register custom interval later.
        // For now schedule hourly to avoid missing interval definitions.
        wp_schedule_event(time() + MINUTE_IN_SECONDS * 5, 'hourly', self::HOOK);
    }

    public function run(): void {
        $processor = ServiceFactory::makeQueueProcessor();
        $processor->process();
    }
}

