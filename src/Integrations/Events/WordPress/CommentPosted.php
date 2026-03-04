<?php

namespace NotificationHub\Integrations\Events\WordPress;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a new comment is posted.
 *
 * @since 1.7.2
 */
final class CommentPosted implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('comment_post', [$this, 'handle'], 10, 3);
    }

    /**
     * @param int $comment_ID
     * @param int|string $comment_approved
     * @param array $commentdata
     */
    public function handle($comment_ID, $comment_approved, $commentdata): void {
        if ((string) $comment_approved === 'spam') {
            return;
        }

        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('comment_posted')
            ->title(sprintf(__('New comment #%d', 'notification-hub'), (int) $comment_ID))
            ->message(__('A new comment was posted.', 'notification-hub'))
            ->status(0)
            ->priority(1)
            ->tags(['comments'])
            ->build();

        $repo->insert($data);
    }
}


