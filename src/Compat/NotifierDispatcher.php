<?php

namespace NotificationHub\Compat;

use NotificationHub\Services\QueueService;
use NotificationHub\Services\ServiceFactory;

/**
 * Back-compat notifier dispatcher.
 *
 * Keeps the old API surface but delegates to new services.
 *
 * @since 1.7.2
 */
class NotifierDispatcher {
    /**
     * Legacy: load handlers.
     */
    public static function load_handlers(): void {
        NotifierLoader::load();
    }

    public function queue_send(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);
        if ($channel === '') {
            return false;
        }

        // Delegate to new dispatcher which uses QueueService.
        $dispatcher = ServiceFactory::makeNotificationDispatcher();
        return $dispatcher->queueSend($channel, $payload);
    }

    public function send(string $channel, array $payload = []): bool {
        return $this->send_now($channel, $payload);
    }

    public function send_now(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);
        if ($channel === '') {
            return false;
        }

        $dispatcher = ServiceFactory::makeNotificationDispatcher();
        return $dispatcher->sendNow($channel, $payload);
    }
}
