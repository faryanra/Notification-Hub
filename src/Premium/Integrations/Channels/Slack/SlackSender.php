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
        $result = $this->sendWithResult($payload);
        return !empty($result['ok']);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{ok:bool,retryable:bool,http_code:int,error:string}
     */
    public function sendWithResult(array $payload): array {
        $pro = $this->settings->getPro();

        $webhook = isset($payload['webhook_url'])
            ? esc_url_raw((string) $payload['webhook_url'])
            : (string) $pro['slack_webhook'];

        $text = isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : '');
        if ($text === '') {
            $text = isset($payload['title']) ? (string) $payload['title'] : '';
        }

        if ($webhook === '' || $text === '') {
            return [
                'ok' => false,
                'retryable' => false,
                'http_code' => 400,
                'error' => 'slack_payload_invalid',
            ];
        }

        $res = wp_remote_post($webhook, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode(['text' => $text]),
        ]);

        if (is_wp_error($res)) {
            return [
                'ok' => false,
                'retryable' => true,
                'http_code' => 0,
                'error' => sanitize_text_field($res->get_error_message()),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        if ($code >= 200 && $code < 300) {
            return [
                'ok' => true,
                'retryable' => false,
                'http_code' => $code,
                'error' => '',
            ];
        }

        $retryable = ($code === 429) || ($code >= 500);
        return [
            'ok' => false,
            'retryable' => $retryable,
            'http_code' => $code,
            'error' => 'slack_http_' . $code,
        ];
    }
}
