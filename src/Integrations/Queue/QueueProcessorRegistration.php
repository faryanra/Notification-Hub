<?php

namespace NotificationHub\Integrations\Queue;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Services\QueueProcessor;
use NotificationHub\Services\ServiceFactory;

/**
 * Register queue processor for nh_process_send.
 *
 * @since 1.7.2
 */
final class QueueProcessorRegistration implements Integration {
    public function register(Loader $loader): void {
        // Lazy-load legacy notifier handlers if they exist.
        if (class_exists('NH_Notifier_Dispatcher') && method_exists('NH_Notifier_Dispatcher', 'load_handlers')) {
            \NH_Notifier_Dispatcher::load_handlers();
        }

        $processor = ServiceFactory::makeQueueProcessor();
        $processor->register();
    }
}
