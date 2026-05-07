<?php

namespace NotificationHub\Channels;

use NotificationHub\Presenters\TemplateLoader;
use NotificationHub\Repositories\SettingsRepository;
use NotificationHub\Services\NotificationFormatter;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Telegram sender (simple Bot API call).
 *
 * Uses channel settings by default; payload can override for testing.
 *
 * @since 1.0.0
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
        $channels = $this->settings->getChannels();
        $payload = (new NotificationFormatter())->format($payload);

        $token = isset($payload['token']) ? trim((string) $payload['token']) : trim((string) $channels['telegram_bot_token']);
        $chatId = isset($payload['chat_id']) ? trim((string) $payload['chat_id']) : trim((string) $channels['telegram_chat_id']);

        $templateData = [
            'title' => isset($payload['title']) ? (string) $payload['title'] : '',
            'summary' => isset($payload['summary']) ? (string) $payload['summary'] : '',
            'link' => isset($payload['link']) ? (string) $payload['link'] : '',
            'context' => isset($payload['context']) && is_array($payload['context']) ? $payload['context'] : [],
            'source_human' => isset($payload['source_human']) ? (string) $payload['source_human'] : '',
            'type_human' => isset($payload['type_human']) ? (string) $payload['type_human'] : '',
            'cta_label' => isset($payload['cta_label']) ? (string) $payload['cta_label'] : '',
        ];

        $text = trim((new TemplateLoader())->render('notifications/telegram.php', $templateData));
        if ($text === '') {
            $text = $this->fallbackText($payload);
        }

        if ($token === '' || $chatId === '' || $text === '') {
            return [
                'ok' => false,
                'retryable' => false,
                'http_code' => 400,
                'error' => 'telegram_payload_invalid',
            ];
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $token);

        $buttonUrl = $this->normalizeInlineButtonUrl(isset($payload['link']) ? (string) $payload['link'] : '');
        $buttonLabel = isset($payload['cta_label']) ? wp_strip_all_tags((string) $payload['cta_label']) : '';
        if ($buttonLabel === '') {
            $buttonLabel = __('Open details', 'notification-hub');
        }

        $requestBody = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => '1',
        ];

        if ($buttonUrl !== '') {
            $requestBody['reply_markup'] = wp_json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => $buttonLabel,
                            'url' => $buttonUrl,
                        ],
                    ],
                ],
            ]);
        }
        $hasReplyMarkup = isset($requestBody['reply_markup']);

        $res = wp_remote_post($url, [
            'timeout' => 15,
            'body' => $requestBody,
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
        $responseBody = (string) wp_remote_retrieve_body($res);
        $decoded = json_decode($responseBody, true);
        $telegramOk = is_array($decoded) && isset($decoded['ok']) && $decoded['ok'] === true;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log(
                sprintf(
                    'Notification Hub Telegram debug: link_exists=%s reply_markup_added=%s code=%d body=%s',
                    $buttonUrl !== '' ? 'yes' : 'no',
                    $hasReplyMarkup ? 'yes' : 'no',
                    $code,
                    substr(sanitize_text_field($responseBody), 0, 500)
                )
            );
        }

        if ($code >= 200 && $code < 300 && $telegramOk) {
            return [
                'ok' => true,
                'retryable' => false,
                'http_code' => $code,
                'error' => '',
            ];
        }

        $retryable = ($code === 429) || ($code >= 500);
        $error = 'telegram_http_' . $code;
        if ($code >= 200 && $code < 300 && !$telegramOk) {
            $error = 'telegram_invalid_response';
        }
        if (is_array($decoded) && !empty($decoded['description']) && is_string($decoded['description'])) {
            $error = sanitize_text_field($decoded['description']);
        }
        return [
            'ok' => false,
            'retryable' => $retryable,
            'http_code' => $code,
            'error' => $error,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function fallbackText(array $payload): string {
        $title = isset($payload['title']) ? wp_strip_all_tags((string) $payload['title']) : '';
        $summary = isset($payload['summary']) ? wp_strip_all_tags((string) $payload['summary']) : '';
        $source = isset($payload['source_human']) ? wp_strip_all_tags((string) $payload['source_human']) : '';
        $type = isset($payload['type_human']) ? wp_strip_all_tags((string) $payload['type_human']) : '';

        if ($title === '') {
            $title = __('New Notification', 'notification-hub');
        }
        if ($summary === '') {
            $summary = $title;
        }
        if ($source === '') {
            $source = __('Unknown', 'notification-hub');
        }
        if ($type === '') {
            $type = __('General', 'notification-hub');
        }
        $lines = [];
        $lines[] = '<b>' . esc_html__('Notification Hub', 'notification-hub') . '</b>';
        $lines[] = '<b>' . esc_html($title) . '</b>';
        $lines[] = esc_html($summary);
        $lines[] = esc_html(sprintf(__('Source: %1$s | Event: %2$s', 'notification-hub'), $source, $type));

        return trim(implode("\n", $lines));
    }

    private function normalizeInlineButtonUrl(string $url): string {
        // Telegram inline buttons require public URLs in production. This filter exists only for local development/testing.
        $allow_local = (bool) apply_filters('notification_hub_allow_local_telegram_buttons', false, $url);

        $url = esc_url_raw(trim($url));
        if ($url === '') {
            return '';
        }

        $parts = wp_parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
        $host = isset($parts['host']) ? strtolower((string) $parts['host']) : '';
        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return '';
        }

        if (!$allow_local) {
            if (in_array($host, ['localhost', '127.0.0.1', '::1'], true) || substr($host, -6) === '.local') {
                return '';
            }

            if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
                $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
                if (filter_var($host, FILTER_VALIDATE_IP, $flags) === false) {
                    return '';
                }
            }
        }

        return $url;
    }
}
