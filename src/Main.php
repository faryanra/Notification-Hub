<?php

namespace NotificationHub;

use NotificationHub\Initializers\Capabilities;
use NotificationHub\Initializers\CronSchedules;
use NotificationHub\Initializers\DatabaseMigration;
use NotificationHub\Initializers\QueueMigration;
use NotificationHub\Integrations\Admin\AdminAjaxRoutesRegistration;
use NotificationHub\Integrations\Admin\AdminAssets;
use NotificationHub\Integrations\Admin\AdminBarBadge;
use NotificationHub\Integrations\Admin\AdminPostRoutesRegistration;
use NotificationHub\Integrations\Admin\MenuRegistration;
use NotificationHub\Integrations\Admin\SettingsRegistration;
use NotificationHub\Integrations\Api\RestRoutesRegistration;
use NotificationHub\Integrations\Cron\CronRegistration;
use NotificationHub\Integrations\Events\EventsRegistration;
use NotificationHub\Integrations\Integration;
use NotificationHub\Integrations\Queue\QueueProcessorRegistration;

/**
 * Main plugin orchestrator.
 *
 * @since 1.7.2
 */
final class Main {
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @since 1.7.2
     */
    public function __construct() {
        $this->loader = new Loader();
    }

    /**
     * Boot plugin integrations.
     *
     * @since 1.7.2
     * @return void
     */
    public function boot(): void {
        $integrations = [
            // Initializers.
            new DatabaseMigration(),
            new Capabilities(),
            new CronSchedules(),
            new QueueMigration(),

            // Cron.
            new CronRegistration(),

            // Queue.
            new QueueProcessorRegistration(),

            // Events.
            new EventsRegistration(),

            // Admin.
            new MenuRegistration(),
            new SettingsRegistration(),
            new AdminAssets(),
            new AdminBarBadge(),
            new AdminAjaxRoutesRegistration(),
            new AdminPostRoutesRegistration(),

            // API.
            new RestRoutesRegistration(),
        ];

        foreach ($integrations as $integration) {
            if ($integration instanceof Integration) {
                $integration->register($this->loader);
            }
        }

        $this->loader->run();
    }
}
