<?php

namespace NotificationHub\Integrations\Events\WordPress;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a post status changes.
 *
 * @since 1.7.2
 */
final class PostStatusChanged implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('transition_post_status', [$this, 'handle'], 10, 3);
    }

    public function handle($new_status, $old_status, $post): void {
        if (!is_object($post) || !isset($post->ID)) {
            return;
        }

        if ((string) $new_status === (string) $old_status) {
            return;
        }

        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('post_status_changed')
            ->title(sprintf(__('Post #%d status changed', 'notification-hub'), (int) $post->ID))
            ->message(sprintf(__('Status: %s ??? %s', 'notification-hub'), (string) $old_status, (string) $new_status))
            ->status(0)
            ->priority(1)
            ->tags(['posts'])
            ->build();

        $repo->insert($data);
    }
}


