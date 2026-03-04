<?php

namespace NotificationHub\Premium\Integrations\Channels\Slack;

use NotificationHub\Repositories\SettingsRepository;

/**
 * Slack sender (Incoming Webhook).
 *
 * Uses SettingsRepository by default; payload can override for testing.
 *
 * @since 1.7.2
 */
final class SlackSender {
    private SettingsRepository $settings;

    public function __construct(?SettingsRepository $settings = null) {
        $this->settings = $settings ?: new SettingsRepository();
    }

    public function send(array $payload): bool {
        $pro = $this->settings->getPro();

        $webhook = isset($payload['webhook_url'])
            ? esc_url_raw((string) $payload['webhook_url'])
            : (string) $pro['slack_webhook'];

        $text = isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : '');
        if ($text === '') {
            $text = isset($payload['title']) ? (string) $payload['title'] : '';
        }

        if ($webhook === '' || $text === '') {
            return false;
        }

        $res = wp_remote_post($webhook, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode(['text' => $text]),
        ]);

        if (is_wp_error($res)) {
            return false;
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        return $code >= 200 && $code < 300;
    }
}
