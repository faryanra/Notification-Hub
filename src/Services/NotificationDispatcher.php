<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Channels\EmailSender;
use NotificationHub\Channels\SlackSender;
use NotificationHub\Channels\TelegramSender;
use NotificationHub\Repositories\SettingsRepository;

/**
 * Notification dispatcher.
 *
 * Responsible for sending notifications to channels (email/telegram/slack) either
 * immediately or via QueueService.
 *
 * @since 1.0.0
 */
final class NotificationDispatcher {
    /**
     * @var SettingsRepository
     */
    private $settings;

    /**
     * @var QueueService
     */
    private $queue;

    /**
     * @var NotificationFormatter
     */
    private $formatter;

    public function __construct(SettingsRepository $settings, QueueService $queue, ?NotificationFormatter $formatter = null) {
        $this->settings = $settings;
        $this->queue    = $queue;
        $this->formatter = $formatter ?: new NotificationFormatter();
    }

    /**
     * Queue notification (preferred).
     *
     * @param string $channel
     * @param array<string,mixed> $payload
     */
    public function queueSend(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);
        if ($channel === '') {
            return false;
        }

        $payload = $this->formatter->format($payload);
        $this->queue->enqueueSend($channel, $payload);
        return true;
    }

    /**
     * Send now (bypass queue).
     *
     * @param string $channel
     * @param array<string,mixed> $payload
     */
    public function sendNow(string $channel, array $payload = []): bool {
        $result = $this->sendNowDetailed($channel, $payload);
        return !empty($result['ok']);
    }

    /**
     * Send now and return diagnostic metadata.
     *
     * @param string $channel
     * @param array<string,mixed> $payload
     * @return array{ok:bool,retryable:bool,http_code:int,error:string}
     */
    public function sendNowDetailed(string $channel, array $payload = []): array {
        $channel = sanitize_key($channel);
        $payload = $this->formatter->format($payload);

        switch ($channel) {
            case 'email':
                $result = $this->sendEmailDetailed($payload);
                break;

            case 'telegram':
                $result = $this->sendTelegramDetailed($payload);
                break;

            case 'slack':
                $result = $this->sendSlackDetailed($payload);
                break;

            default:
                $result = [
                    'ok' => false,
                    'retryable' => false,
                    'http_code' => 400,
                    'error' => 'unknown_channel',
                ];
                break;
        }

        if (!empty($result['ok'])) {
            EventLogger::info('channel', 'channel_send_ok', 'Channel send succeeded', [
                'channel' => $channel,
            ]);
        } else {
            EventLogger::warn('channel', 'channel_send_failed', 'Channel send failed', [
                'channel' => $channel,
                'retryable' => !empty($result['retryable']),
                'http_code' => isset($result['http_code']) ? (int) $result['http_code'] : 0,
                'error' => isset($result['error']) ? (string) $result['error'] : 'send_failed',
            ]);
        }

        return [
            'ok' => !empty($result['ok']),
            'retryable' => !empty($result['retryable']),
            'http_code' => isset($result['http_code']) ? (int) $result['http_code'] : 0,
            'error' => isset($result['error']) ? (string) $result['error'] : '',
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendEmailDetailed(array $payload): array {
        $general = $this->settings->getGeneral();

        if (empty($payload['to']) && !empty($general['email_to'])) {
            $payload['to'] = $general['email_to'];
        }

        $sender = new EmailSender();
        if (method_exists($sender, 'sendWithResult')) {
            return (array) $sender->sendWithResult($payload);
        }

        $ok = $sender->send($payload);
        return [
            'ok' => (bool) $ok,
            'retryable' => !$ok,
            'http_code' => 0,
            'error' => $ok ? '' : 'email_send_failed',
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendTelegramDetailed(array $payload): array {
        $sender = new TelegramSender();
        if (method_exists($sender, 'sendWithResult')) {
            return (array) $sender->sendWithResult($payload);
        }

        $ok = (bool) $sender->send($payload);
        return [
            'ok' => $ok,
            'retryable' => !$ok,
            'http_code' => 0,
            'error' => $ok ? '' : 'telegram_send_failed',
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendSlackDetailed(array $payload): array {
        $sender = new SlackSender();
        if (method_exists($sender, 'sendWithResult')) {
            return (array) $sender->sendWithResult($payload);
        }

        $ok = (bool) $sender->send($payload);
        return [
            'ok' => $ok,
            'retryable' => !$ok,
            'http_code' => 0,
            'error' => $ok ? '' : 'slack_send_failed',
        ];
    }
}

