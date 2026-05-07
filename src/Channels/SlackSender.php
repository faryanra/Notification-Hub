<?php
namespace NotificationHub\Channels;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Presenters\TemplateLoader;
use NotificationHub\Repositories\SettingsRepository;
use NotificationHub\Services\NotificationFormatter;

/**
 * Slack sender (Incoming Webhook).
 *
 * Uses channel settings by default; payload can override for testing.
 *
 * @since 1.0.0
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
        $channels = $this->settings->getChannels();
        $payload = (new NotificationFormatter())->format($payload);

        $webhook = isset($payload['webhook_url'])
            ? esc_url_raw((string) $payload['webhook_url'])
            : (string) $channels['slack_webhook'];

        $templateData = [
            'title' => isset($payload['title']) ? (string) $payload['title'] : '',
            'summary' => isset($payload['summary']) ? (string) $payload['summary'] : '',
            'link' => isset($payload['link']) ? (string) $payload['link'] : '',
            'context' => isset($payload['context']) && is_array($payload['context']) ? $payload['context'] : [],
            'source_human' => isset($payload['source_human']) ? (string) $payload['source_human'] : '',
            'type_human' => isset($payload['type_human']) ? (string) $payload['type_human'] : '',
            'cta_label' => isset($payload['cta_label']) ? (string) $payload['cta_label'] : '',
        ];

        $text = trim((new TemplateLoader())->render('notifications/slack.php', $templateData));
        if ($text === '') {
            $text = $this->fallbackText($payload);
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

    /**
     * @param array<string,mixed> $payload
     */
    private function fallbackText(array $payload): string {
        $title = isset($payload['title']) ? wp_strip_all_tags((string) $payload['title']) : '';
        $summary = isset($payload['summary']) ? wp_strip_all_tags((string) $payload['summary']) : '';
        $source = isset($payload['source_human']) ? wp_strip_all_tags((string) $payload['source_human']) : '';
        $type = isset($payload['type_human']) ? wp_strip_all_tags((string) $payload['type_human']) : '';
        $link = isset($payload['link']) ? esc_url_raw((string) $payload['link']) : '';
        $cta = isset($payload['cta_label']) ? wp_strip_all_tags((string) $payload['cta_label']) : '';

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
        if ($cta === '') {
            $cta = __('Open details', 'notification-hub');
        }

        $lines = [];
        $lines[] = '[Notification Hub]';
        $lines[] = $title;
        $lines[] = '';
        $lines[] = $summary;
        $lines[] = '';
        $lines[] = sprintf(__('Source: %1$s | Event: %2$s', 'notification-hub'), $source, $type);

        if ($link !== '') {
            $lines[] = sprintf(__('Action: <%1$s|%2$s>', 'notification-hub'), $link, $cta);
        }

        return trim(implode("\n", $lines));
    }
}
