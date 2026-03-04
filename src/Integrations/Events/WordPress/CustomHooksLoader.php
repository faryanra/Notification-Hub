<?php

namespace NotificationHub\Integrations\Events\WordPress;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\CustomHooksRepository;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Loads active custom hooks from DB (nh_hooks) and registers them as WP actions.
 *
 * When a custom action is fired, a notification is created.
 *
 * @since 1.7.2
 */
final class CustomHooksLoader implements Integration {
    public function register(Loader $loader): void {
        // Register on init to ensure DB and plugins are loaded.
        $loader->addAction('init', [$this, 'registerHooks']);
    }

    public function registerHooks(): void {
        $repo = new CustomHooksRepository();
        $hooks = $repo->listActive();

        if (!$hooks) {
            return;
        }

        foreach ($hooks as $hook) {
            $action = isset($hook['action_name']) ? sanitize_text_field((string) $hook['action_name']) : '';
            if ($action === '') {
                continue;
            }

            // Register listener for this custom hook action.
            add_action($action, function ($payload = null) use ($hook, $action) {
                $title = isset($hook['title']) ? (string) $hook['title'] : $action;

                $message = __('Custom hook triggered.', 'notification-hub');
                if (is_array($payload) && isset($payload['message']) && is_string($payload['message']) && $payload['message'] !== '') {
                    $message = $payload['message'];
                }

                $tags = ['custom_hook'];
                if (isset($hook['channels']) && is_string($hook['channels']) && $hook['channels'] !== '') {
                    $tags[] = 'channels_configured';
                }

                $data = NotificationBuilder::make()
                    ->source('custom_hook')
                    ->type('custom_hook_triggered')
                    ->title($title)
                    ->message($message)
                    ->status(0)
                    ->priority(50)
                    ->tags($tags)
                    ->context([
                        'action' => $action,
                        'hook_id' => isset($hook['id']) ? (int) $hook['id'] : 0,
                    ])
                    ->build();

                (new NotificationsRepository())->insert($data);
            }, 10, 1);
        }
    }
}


