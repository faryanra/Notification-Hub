<?php
namespace NotificationHub\Integrations\Queue;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Services\QueueProcessor;
use NotificationHub\Services\ServiceFactory;

/**
 * Register queue processor for nh_process_send.
 *
 * @since 1.0.0
 */
final class QueueProcessorRegistration implements Integration {
    public function register(Loader $loader): void {
        $processor = ServiceFactory::makeQueueProcessor();
        $processor->register();
    }
}

