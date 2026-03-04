<?php

namespace NotificationHub\Integrations\Events\WordPress;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a new user registers.
 *
 * @since 1.7.2
 */
final class UserRegistered implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('user_register', [$this, 'handle'], 10, 1);
    }

    public function handle($user_id): void {
        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('user_registered')
            ->title(sprintf(__('New user registered #%d', 'notification-hub'), (int) $user_id))
            ->message(__('A new user registered.', 'notification-hub'))
            ->status(0)
            ->priority(1)
            ->tags(['users'])
            ->build();

        $repo->insert($data);
    }
}


