<?php
namespace NotificationHub\Integrations\Cron;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Registers cron-related integrations.
 *
 * @since 1.0.0
 */
final class CronRegistration implements Integration {
    public function register(Loader $loader): void {
        (new CleanupOldNotifications())->register($loader);
        (new ProcessQueue())->register($loader);
    }
}

