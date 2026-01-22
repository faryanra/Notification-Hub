<?php
/**
 * NH_Notifier_Dispatcher
 *
 * Notification dispatcher that routes messages to channel handlers (email/telegram/slack),
 * optionally via queue, and applies multisite network policy.
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier_Dispatcher {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var mixed
     */
    protected $registry;

    /**
     * Queue handler.
     *
     * @since 1.6.2
     * @var NH_Notifier_Queue|null
     */
    protected $queue;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry/container instance.
     */
    public function __construct($registry = null) {
        $this->registry = $registry;

        $this->queue = class_exists('NH_Notifier_Queue')
            ? new NH_Notifier_Queue($registry)
            : null;
    }

    /**
     * Load internal notifier handlers.
     *
     * @since 1.6.2
     */
    public static function load_handlers(): void {
        if (class_exists('NH_Notifier_Loader')) {
            NH_Notifier_Loader::load();
        }
    }

    /**
     * Queue notification (preferred method).
     *
     * @since 1.6.2
     */
    public function queue_send(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);

        if (!$this->queue) {
            return $this->send_now($channel, $payload);
        }

        return (bool) $this->queue->queue_send($channel, $payload);
    }

    /**
     * Legacy alias.
     *
     * @since 1.6.2
     */
    public function send(string $channel, array $payload = []): bool {
        return $this->send_now($channel, $payload);
    }

    /**
     * Send immediately (bypass queue).
     *
     * @since 1.6.2
     */
    public function send_now(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);

        if (!$this->check_network_policy($channel, $payload)) {
            return false;
        }

        $success = $this->dispatch($channel, $payload);

        if ($this->queue && isset($payload['notification_id'])) {
            $this->queue->log_delivery_status(
                (int) $payload['notification_id'],
                $channel,
                (bool) $success,
                $success ? '' : esc_html__('Delivery failed', 'notification-hub')
            );
        }

        return (bool) $success;
    }

    /**
     * Dispatch to appropriate handler.
     *
     * @since 1.6.2
     */
    protected function dispatch(string $channel, array $payload): bool {
        switch ($channel) {
            case 'email':
                if (!empty($payload['override_email_to']) && is_string($payload['override_email_to'])) {
                    $payload['to'] = sanitize_email($payload['override_email_to']);
                }

                if (!class_exists('NH_Notifier_Email')) {
                    $this->debug_log(sprintf('Notification Hub: Email handler missing (%s)', 'NH_Notifier_Email'));
                    return false;
                }

                return (bool) NH_Notifier_Email::send($payload);

            case 'telegram':
                return $this->send_pro_channel(
                    'telegram',
                    'Telegram',
                    static function () use ($payload) {
                        return class_exists('NH_Notifier_Telegram') ? (bool) NH_Notifier_Telegram::send($payload) : false;
                    }
                );

            case 'slack':
                return $this->send_pro_channel(
                    'slack',
                    'Slack',
                    static function () use ($payload) {
                        return class_exists('NH_Notifier_Slack') ? (bool) NH_Notifier_Slack::send($payload) : false;
                    }
                );

            default:
                $this->debug_log(sprintf('Notification Hub: Unknown channel %s', $channel));
                return false;
        }
    }

    /**
     * Send to Pro-only channel.
     *
     * @since 1.7.1
     */
    protected function send_pro_channel(string $cap, string $name, callable $sender): bool {
        $cap = sanitize_key($cap);

        if (!class_exists('NH_License') || !method_exists('NH_License', 'can') || !NH_License::can($cap)) {
            $this->debug_log(
                sprintf(
                    /* translators: %s: channel name */
                    __('%s requires Pro', 'notification-hub'),
                    $name
                )
            );
            return false;
        }

        return (bool) call_user_func($sender);
    }

    /**
     * Check network policy (Multisite).
     *
     * @since 1.6.2
     */
    protected function check_network_policy(string $channel, array &$payload): bool {
        if (!is_multisite()) {
            return true;
        }

        $policy = get_site_option('nh_network_policy', []);
        $policy = is_array($policy) ? $policy : [];

        if (!empty($policy['channels']) && is_array($policy['channels']) && !in_array($channel, $policy['channels'], true)) {
            $this->debug_log(sprintf('Notification Hub: Channel blocked by network policy (%s)', $channel));
            return false;
        }

        if ($channel === 'email' && !empty($policy['email_to']) && is_string($policy['email_to'])) {
            $payload['override_email_to'] = sanitize_email($policy['email_to']);
        }

        return true;
    }

    /**
     * Debug logger.
     *
     * @since 1.6.2
     */
    protected function debug_log(string $message): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($message);
        }
    }
}