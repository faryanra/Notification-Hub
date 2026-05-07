<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\SettingsRepository;

/**
 * Service factory.
 *
 * @since 1.0.0
 */
final class ServiceFactory {
    public static function makeQueueService(): QueueService {
        return new QueueService(
            static function (): bool {
                $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
                if ($host === '') {
                    $host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
                }

                $host = strtolower(trim($host));
                if ($host === '') {
                    return false;
                }

                $portPos = strpos($host, ':');
                if ($portPos !== false) {
                    $host = substr($host, 0, $portPos);
                }

                if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                    return true;
                }

                return substr($host, -6) === '.local' || substr($host, -5) === '.test';
            }
        );
    }

    public static function makeNotificationDispatcher(): NotificationDispatcher {
        return new NotificationDispatcher(new SettingsRepository(), self::makeQueueService());
    }

    public static function makeQueueProcessor(): QueueProcessor {
        return new QueueProcessor(self::makeNotificationDispatcher());
    }
}

