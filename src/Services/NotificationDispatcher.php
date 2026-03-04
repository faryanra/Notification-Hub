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
        $channel = sanitize_key($channel);

        switch ($channel) {
            case 'email':
                return $this->sendEmail($payload);

            case 'telegram':
                return $this->sendTelegram($payload);

            case 'slack':
                return $this->sendSlack($payload);

            default:
                $this->debugLog(sprintf('Notification Hub: Unknown channel %s', $channel));
                return false;
        }
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendEmail(array $payload): bool {
        $general = $this->settings->getGeneral();

        if (empty($payload['to']) && !empty($general['email_to'])) {
            $payload['to'] = $general['email_to'];
        }

        // Prefer new premium sender if implemented.
        if (class_exists(EmailSender::class)) {
            $ok = (new EmailSender())->send($payload);
            if ($ok) {
                return true;
            }
        }

        if (class_exists('NH_Notifier_Email')) {
            return (bool) \NH_Notifier_Email::send($payload);
        }

        // Fallback to wp_mail.
        $to = isset($payload['to']) ? (string) $payload['to'] : get_option('admin_email');
        $subject = isset($payload['subject']) ? (string) $payload['subject'] : __('Notification Hub', 'notification-hub');
        $body = isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : '');

        return ($to !== '' && $subject !== '' && $body !== '') ? (bool) wp_mail($to, $subject, $body) : false;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendTelegram(array $payload): bool {
        if (class_exists(TelegramSender::class)) {
            return (bool) (new TelegramSender())->send($payload);
        }

        if (class_exists('NH_Notifier_Telegram')) {
            return (bool) \NH_Notifier_Telegram::send($payload);
        }

        $this->debugLog('Notification Hub: Telegram sender missing');
        return false;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendSlack(array $payload): bool {
        if (class_exists(SlackSender::class)) {
            return (bool) (new SlackSender())->send($payload);
        }

        if (class_exists('NH_Notifier_Slack')) {
            return (bool) \NH_Notifier_Slack::send($payload);
        }

        $this->debugLog('Notification Hub: Slack sender missing');
        return false;
    }

    private function debugLog(string $message): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($message);
        }
    }
}
