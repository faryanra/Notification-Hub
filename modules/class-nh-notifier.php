<?php
/**
 * Notification Dispatcher
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

// Load handlers
require_once __DIR__ . '/notifier/class-nh-notifier-queue.php';
require_once __DIR__ . '/notifier/class-nh-notifier-email.php';
require_once __DIR__ . '/notifier/class-nh-notifier-telegram.php';
require_once __DIR__ . '/notifier/class-nh-notifier-slack.php';

class NH_Notifier {

    private $registry;
    private $queue;

    public function __construct($registry = null) {
        $this->registry = $registry;
        $this->queue = new NH_Notifier_Queue($registry);
    }

    /**
     * Queue notification (preferred method)
     */
    public function queue_send(string $channel, array $payload = []): bool {
        return $this->queue->queue_send($channel, $payload);
    }

    /**
     * Legacy alias
     */
    public function send($channel, $payload = []): bool {
        return $this->send_now($channel, $payload);
    }

    /**
     * Send immediately (bypass queue)
     */
    public function send_now(string $channel, array $payload = []): bool {
        $channel = sanitize_key($channel);

        // Apply network policy
        if (!$this->check_network_policy($channel, $payload)) {
            return false;
        }

        $success = $this->dispatch($channel, $payload);

        // Log delivery status
        if (isset($payload['notification_id'])) {
            $this->queue->log_delivery_status(
                (int)$payload['notification_id'],
                $channel,
                $success,
                $success ? '' : 'Delivery failed'
            );
        }

        return $success;
    }

    /**
     * Dispatch to appropriate handler
     */
    private function dispatch(string $channel, array $payload): bool {
        switch ($channel) {
            case 'email':
                return NH_Notifier_Email::send($payload);

            case 'telegram':
                return $this->send_pro_channel('Telegram', function() use ($payload) {
                    return NH_Notifier_Telegram::send($payload);
                });

            case 'slack':
                return $this->send_pro_channel('Slack', function() use ($payload) {
                    return NH_Notifier_Slack::send($payload);
                });

            default:
                error_log("❌ Unknown channel: {$channel}");
                return false;
        }
    }

    /**
     * Send to Pro-only channel
     */
    private function send_pro_channel(string $name, callable $sender): bool {
        if (!class_exists('NH_License') || !NH_License::is_pro()) {
            if (WP_DEBUG) {
                error_log("🚫 {$name} requires Pro license");
            }
            return false;
        }

        return $sender();
    }

    /**
     * Check network policy (Multisite)
     */
    private function check_network_policy(string $channel, array &$payload): bool {
        if (!is_multisite()) {
            return true;
        }

        $policy = get_site_option('nh_network_policy', []);

        // Check allowed channels
        if (!empty($policy['channels']) && !in_array($channel, $policy['channels'], true)) {
            if (WP_DEBUG) {
                error_log("🚫 Channel blocked by network policy: {$channel}");
            }
            return false;
        }

        // Override email recipient
        if ($channel === 'email' && !empty($policy['email_to'])) {
            $payload['override_email_to'] = sanitize_email($policy['email_to']);
        }

        return true;
    }
}
