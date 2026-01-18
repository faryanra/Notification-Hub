<?php
/**
 * NH_Notifier
 *
 * Notification dispatcher that routes messages to channel handlers (email/telegram/slack),
 * optionally via queue, and applies multisite network policy.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var mixed
     */
    private $registry;

    /**
     * Queue handler.
     *
     * @since 1.6.2
     * @var NH_Notifier_Queue|null
     */
    private $queue;

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
     * Load internal notifier handlers (if present).
     *
     * @since 1.6.2
     * @return void
     */
    public static function load_handlers(): void {
        self::safe_require_once(__DIR__ . '/notifier/class-nh-notifier-queue.php');
        self::safe_require_once(__DIR__ . '/notifier/class-nh-notifier-email.php');
        self::safe_require_once(__DIR__ . '/notifier/class-nh-notifier-telegram.php');
        self::safe_require_once(__DIR__ . '/notifier/class-nh-notifier-slack.php');
    }

    /**
     * Safe require helper for internal notifier handlers.
     *
     * @since 1.6.2
     * @param string $path Absolute file path.
     * @return void
     */
    private static function safe_require_once(string $path): void {
        if (file_exists($path)) {
            require_once $path;
            return;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log(sprintf('Notification Hub: Missing notifier handler file %s', $path));
        }
    }

    /**
     * Queue notification (preferred method).
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload.
     * @return bool
     */
    public function queue_send(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);

        if (!$this->queue) {
            // Queue not available, fallback to immediate send.
            return $this->send_now($channel, $payload);
        }

        return (bool) $this->queue->queue_send($channel, $payload);
    }

    /**
     * Legacy alias.
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload.
     * @return bool
     */
    public function send(string $channel, array $payload = []): bool {
        return $this->send_now($channel, $payload);
    }

    /**
     * Send immediately (bypass queue).
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload.
     * @return bool
     */
    public function send_now(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);

        // Apply network policy.
        if (!$this->check_network_policy($channel, $payload)) {
            return false;
        }

        $success = $this->dispatch($channel, $payload);

        // Log delivery status (if queue supports it).
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
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload.
     * @return bool
     */
    private function dispatch(string $channel, array $payload): bool {
        switch ($channel) {
            case 'email':
                // Respect network override recipient if provided.
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
     * @since 1.6.2
     * @param string   $capability Capability slug.
     * @param string   $name Channel label.
     * @param callable $sender Callback that performs send.
     * @return bool
     */
    private function send_pro_channel(string $capability, string $name, callable $sender): bool {
        if (!class_exists('NH_License') || !method_exists('NH_License', 'can') || !NH_License::can($capability)) {
            $this->debug_log(
                sprintf(
                    /* translators: %s: channel name */
                    __('%s requires Pro license', 'notification-hub'),
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
     * @param string $channel Channel slug.
     * @param array  $payload Notification payload (may be modified).
     * @return bool
     */
    private function check_network_policy(string $channel, array &$payload): bool {
        if (!is_multisite()) {
            return true;
        }

        $policy = get_site_option('nh_network_policy', []);
        $policy = is_array($policy) ? $policy : [];

        // Check allowed channels.
        if (!empty($policy['channels']) && is_array($policy['channels']) && !in_array($channel, $policy['channels'], true)) {
            $this->debug_log(sprintf('Notification Hub: Channel blocked by network policy (%s)', $channel));
            return false;
        }

        // Override email recipient.
        if ($channel === 'email' && !empty($policy['email_to']) && is_string($policy['email_to'])) {
            $payload['override_email_to'] = sanitize_email($policy['email_to']);
        }

        return true;
    }

    /**
     * Debug logger.
     *
     * @since 1.6.2
     * @param string $message Log message.
     * @return void
     */
    private function debug_log(string $message): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($message);
        }
    }
}

// Load handlers on file load (back-compat).
NH_Notifier::load_handlers();