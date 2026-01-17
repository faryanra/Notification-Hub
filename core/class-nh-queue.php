<?php
/**
 * NH_Queue
 *
 * Queue handler for scheduling and processing async notification delivery.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Queue {

    /**
     * Schedule an async job to deliver a notification via a given channel.
     *
     * Uses Action Scheduler when available; otherwise falls back to WP-Cron.
     * In localhost environments, it can run immediately for developer convenience.
     *
     * @since 1.6.2
     * @param string $channel e.g. 'email', 'telegram', 'slack'.
     * @param array  $payload Payload for notifier.
     * @return void
     */
    public static function enqueue_send(string $channel, array $payload): void {

        /**
         * Dev convenience: run immediately on localhost (no delay, no cron).
         */
        if (self::is_localhost()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log(sprintf('NH_Queue: localhost detected — processing "%s" immediately', $channel));
            }

            $registry = (class_exists('NH_Core_Registry') && method_exists('NH_Core_Registry', 'get'))
                ? NH_Core_Registry::get()
                : null;

            $notifier = ($registry && method_exists($registry, 'get_svc'))
                ? $registry->get_svc('notifier')
                : null;

            if ($notifier && method_exists($notifier, 'send_now')) {
                $notifier->send_now($channel, $payload);
            }

            return;
        }

        /**
         * Production: schedule async delivery.
         */
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action(
                'nh_process_send',
                [
                    'channel' => $channel,
                    'payload' => $payload,
                ]
            );
            return;
        }

        /**
         * Fallback: WP-Cron single event.
         */
        wp_schedule_single_event(
            time() + 3,
            'nh_process_send',
            [
                'channel' => $channel,
                'payload' => $payload,
            ]
        );
    }

    /**
     * Register the processor that executes queued jobs.
     *
     * MUST be called once during plugin boot (from Loader).
     *
     * @since 1.6.2
     * @param object $registry NH_Core_Registry instance.
     * @return void
     */
    public static function hook_processor($registry): void {
        add_action(
            'nh_process_send',
            function ($channel, $payload) use ($registry) {
                $notifier = is_object($registry) ? $registry->get_svc('notifier') : null;
                if (!$notifier) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        error_log('NH_Queue: notifier not found in registry');
                    }
                    return;
                }

                $notifier->send_now((string) $channel, (array) $payload);
            },
            10,
            2
        );
    }

    /**
     * Detect if environment is localhost (dev mode).
     *
     * @since 1.6.2
     * @return bool
     */
    protected static function is_localhost(): bool {
        $host   = isset($_SERVER['HTTP_HOST']) ? strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']))) : '';
        $server = isset($_SERVER['SERVER_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'])) : '';

        if (in_array($host, ['localhost', '127.0.0.1'], true)) {
            return true;
        }
        if (in_array($server, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        // PHP-compat: avoid str_contains/str_ends_with.
        if ($host !== '') {
            if (substr($host, -5) === '.test') {
                return true;
            }
            if (strpos($host, '.local') !== false) {
                return true;
            }
        }

        return false;
    }
}