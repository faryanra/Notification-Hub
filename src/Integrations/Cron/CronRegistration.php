<?php

namespace NotificationHub\Integrations\Cron;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Registers cron-related integrations.
 *
 * @since 1.7.2
 */
final class CronRegistration implements Integration {
    public function register(Loader $loader): void {
        (new CleanupOldNotifications())->register($loader);
        (new ProcessQueue())->register($loader);
    }
}
