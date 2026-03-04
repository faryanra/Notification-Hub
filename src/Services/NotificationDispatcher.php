<?php

namespace NotificationHub\Services;

use NotificationHub\Premium\Integrations\Channels\Email\EmailSender;
use NotificationHub\Premium\Integrations\Channels\Slack\SlackSender;
use NotificationHub\Premium\Integrations\Channels\Telegram\TelegramSender;
use NotificationHub\Repositories\SettingsRepository;

/**
 * Notification dispatcher.
 *
 * Responsible for sending notifications to channels (email/telegram/slack) either
 * immediately or via QueueService.
 *
 * @since 1.7.2
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

    public function __construct(SettingsRepository $settings, QueueService $queue) {
        $this->settings = $settings;
        $this->queue    = $queue;
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

        // Prefer new premium sender if implemented.
        if (class_exists(EmailSender::class)) {
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

        if (class_exists('NH_Notifier_Email')) {
            $ok = (bool) \NH_Notifier_Email::send($payload);
            return [
                'ok' => $ok,
                'retryable' => !$ok,
                'http_code' => 0,
                'error' => $ok ? '' : 'legacy_email_send_failed',
            ];
        }

        // Fallback to wp_mail.
        $to = isset($payload['to']) ? (string) $payload['to'] : get_option('admin_email');
        $subject = isset($payload['subject']) ? (string) $payload['subject'] : __('Notification Hub', 'notification-hub');
        $body = isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : '');

        if ($to === '' || $subject === '' || $body === '') {
            return [
                'ok' => false,
                'retryable' => false,
                'http_code' => 400,
                'error' => 'email_payload_invalid',
            ];
        }

        $ok = (bool) wp_mail($to, $subject, $body);
        return [
            'ok' => $ok,
            'retryable' => !$ok,
            'http_code' => 0,
            'error' => $ok ? '' : 'wp_mail_failed',
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendTelegramDetailed(array $payload): array {
        if (class_exists(TelegramSender::class)) {
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

        if (class_exists('NH_Notifier_Telegram')) {
            $ok = (bool) \NH_Notifier_Telegram::send($payload);
            return [
                'ok' => $ok,
                'retryable' => !$ok,
                'http_code' => 0,
                'error' => $ok ? '' : 'legacy_telegram_send_failed',
            ];
        }

        return [
            'ok' => false,
            'retryable' => false,
            'http_code' => 500,
            'error' => 'telegram_sender_missing',
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendSlackDetailed(array $payload): array {
        if (class_exists(SlackSender::class)) {
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

        if (class_exists('NH_Notifier_Slack')) {
            $ok = (bool) \NH_Notifier_Slack::send($payload);
            return [
                'ok' => $ok,
                'retryable' => !$ok,
                'http_code' => 0,
                'error' => $ok ? '' : 'legacy_slack_send_failed',
            ];
        }

        return [
            'ok' => false,
            'retryable' => false,
            'http_code' => 500,
            'error' => 'slack_sender_missing',
        ];
    }
}
