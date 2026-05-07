<?php
namespace NotificationHub\Integrations\Api;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Routes\Api\DeleteNotification;
use NotificationHub\Routes\Api\GetNotifications;
use NotificationHub\Routes\Api\GetMetrics;
use NotificationHub\Routes\Api\TestTrigger;
use NotificationHub\Routes\Api\UpdateNotification;
use NotificationHub\Routes\Api\Webhook;
use NotificationHub\Security\RestGuard;

/**
 * Register REST API routes.
 *
 * @since 1.0.0
 */
final class RestRoutesRegistration implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void {
        $this->requireRoute(GetNotifications::class, 'Routes/Api/GetNotifications.php');
        $this->requireRoute(GetMetrics::class, 'Routes/Api/GetMetrics.php');
        $this->requireRoute(TestTrigger::class, 'Routes/Api/TestTrigger.php');
        $this->requireRoute(UpdateNotification::class, 'Routes/Api/UpdateNotification.php');
        $this->requireRoute(DeleteNotification::class, 'Routes/Api/DeleteNotification.php');
        $this->requireRoute(Webhook::class, 'Routes/Api/Webhook.php');

        register_rest_route('nh/v1', '/notifications', [
            'methods'             => 'GET',
            'permission_callback' => [RestGuard::class, 'requireAdminAndNonce'],
            'args'                => [
                'since'          => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'status'         => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'source'         => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'type'           => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'min_priority'   => ['type' => 'integer', 'sanitize_callback' => 'absint'],
                'tags'           => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'only_important' => ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean'],
                'limit'          => ['type' => 'integer', 'default' => 50, 'sanitize_callback' => 'absint'],
                'offset'         => ['type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint'],
            ],
            'callback'            => [new GetNotifications(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/notifications/(?P<id>\d+)', [
            'methods'             => 'POST',
            'permission_callback' => [RestGuard::class, 'requireAdminAndNonce'],
            'args'                => [
                'action' => ['type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_key'],
            ],
            'callback'            => [new UpdateNotification(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/notifications/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'permission_callback' => [RestGuard::class, 'requireAdminAndNonce'],
            'callback'            => [new DeleteNotification(), 'handle'],
        ]);

        register_rest_route('nh/v1', '/metrics', [
            'methods'             => 'GET',
            'permission_callback' => [RestGuard::class, 'requireAdminAndNonce'],
            'args'                => [
                'range' => [
                    'type'              => 'string',
                    'required'          => false,
                    'default'           => '7d',
                    'sanitize_callback' => 'sanitize_key',
                ],
            ],
            'callback'            => [new GetMetrics(), 'handle'],
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
            'permission_callback' => [RestGuard::class, 'requireAdminAndNonce'],
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

