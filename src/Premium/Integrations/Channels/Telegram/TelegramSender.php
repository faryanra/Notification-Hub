<?php

namespace NotificationHub\Premium\Integrations\Channels\Telegram;

use NotificationHub\Repositories\SettingsRepository;

/**
 * Telegram sender (simple Bot API call).
 *
 * Uses SettingsRepository by default; payload can override for testing.
 *
 * @since 1.7.2
 */
final class TelegramSender {
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

        $token = isset($payload['token']) ? (string) $payload['token'] : (string) $pro['telegram_bot_token'];
        $chatId = isset($payload['chat_id']) ? (string) $payload['chat_id'] : (string) $pro['telegram_chat_id'];

        $text = isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : '');
        if ($text === '') {
            $text = isset($payload['title']) ? (string) $payload['title'] : '';
        }

        if ($token === '' || $chatId === '' || $text === '') {
            return [
                'ok' => false,
                'retryable' => false,
                'http_code' => 400,
                'error' => 'telegram_payload_invalid',
            ];
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', rawurlencode($token));

        $res = wp_remote_post($url, [
            'timeout' => 15,
            'body' => [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => '1',
            ],
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
            'error' => 'telegram_http_' . $code,
        ];
    }
}
