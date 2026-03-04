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
        $pro = $this->settings->getPro();

        $token = isset($payload['token']) ? (string) $payload['token'] : (string) $pro['telegram_bot_token'];
        $chatId = isset($payload['chat_id']) ? (string) $payload['chat_id'] : (string) $pro['telegram_chat_id'];

        $text = isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : '');
        if ($text === '') {
            $text = isset($payload['title']) ? (string) $payload['title'] : '';
        }

        if ($token === '' || $chatId === '' || $text === '') {
            return false;
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
            return false;
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        return $code >= 200 && $code < 300;
    }
}
