<?php

namespace NotificationHub\Integrations\Api;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Routes\Api\DeleteNotification;
use NotificationHub\Routes\Api\GetNotifications;
use NotificationHub\Routes\Api\TestTrigger;
use NotificationHub\Routes\Api\UpdateNotification;
use NotificationHub\Routes\Api\Webhook;

/**
 * Register REST API routes.
 *
 * @since 1.7.2
 */
final class RestRoutesRegistration implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void {
        $this->requireRoute(GetNotifications::class, 'Routes/Api/GetNotifications.php');
        $this->requireRoute(TestTrigger::class, 'Routes/Api/TestTrigger.php');
        $this->requireRoute(UpdateNotification::class, 'Routes/Api/UpdateNotification.php');
        $this->requireRoute(DeleteNotification::class, 'Routes/Api/DeleteNotification.php');
        $this->requireRoute(Webhook::class, 'Routes/Api/Webhook.php');

        register_rest_route('nh/v1', '/notifications', [
            'methods'             => 'GET',
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
            'args'                => [
                'since'          => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'status'         => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'source'         => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'type'           => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'min_priority'   => ['type' => 'integer', 'sanitize_callback' => 'absint'],
                'tags'           => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'only_important' => ['type' => 'boolean', 'sanitize_callback' => static fn($v) => (bool) $v],
                'limit'          => ['type' => 'integer', 'default' => 50, 'sanitize_callback' => 'absint'],
                'offset'         => ['type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint'],
            ],
            'callback'            => [new GetNotifications(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/notifications/(?P<id>\d+)', [
            'methods'             => 'POST',
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
            'args'                => [
                'action' => ['type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_key'],
            ],
            'callback'            => [new UpdateNotification(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/notifications/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
            'callback'            => [new DeleteNotification(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/webhook', [
            'methods'             => 'POST',
            'permission_callback' => static function () {
                // Auth is handled via shared secret header; allow anonymous.
                return true;
            },
            'callback'            => [new Webhook(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/test-trigger/(?P<id>\d+)', [
            'methods'             => 'POST',
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
            'callback'            => [new TestTrigger(), 'handle'],
        ]);
    }

    private function requireRoute(string $class, string $relPath): void {
        if (!class_exists($class)) {
            $file = NH_SRC_DIR . $relPath;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}
