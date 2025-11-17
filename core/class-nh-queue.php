<?php
// Queue handler
// Responsible for scheduling and processing async notification delivery.

if (!defined('ABSPATH')) exit;

class NH_Queue {

    /**
     * Schedule an async job to deliver a notification via a given channel.
     *
     * @param string $channel  e.g. 'email', 'telegram', 'slack'
     * @param array  $payload  ['title' => '...', 'body' => '...', 'source' => '...']
     */
    public static function enqueue_send(string $channel, array $payload) {

        // ✅ DEV MODE: run immediately on localhost (no delay, no cron)
        if (self::is_localhost()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("⚡ NH_Queue: localhost detected — processing {$channel} instantly");
            }
            $notifier = NH_Core_Registry::get()->get_svc('notifier');
            if ($notifier) {
                $notifier->send_now($channel, $payload);
            }
            return;
        }

        // ✅ Production mode — schedule async delivery
        if (function_exists('as_enqueue_async_action')) {
            // via ActionScheduler (preferred)
            as_enqueue_async_action(
                'nh_process_send',
                [
                    'channel' => $channel,
                    'payload' => $payload,
                ]
            );

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("NH_Queue: job enqueued via ActionScheduler for {$channel}");
            }

        } else {
            // fallback: WP-Cron single event
            wp_schedule_single_event(
                time() + 3,
                'nh_process_send',
                [
                    'channel' => $channel,
                    'payload' => $payload,
                ]
            );

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("NH_Queue: job scheduled via WP-Cron for {$channel}");
            }
        }
    }

    /**
     * Register the processor that actually executes the queued job.
     * MUST be called once during plugin boot (from Loader).
     *
     * @param object $registry  NH_Core_Registry instance
     */
    public static function hook_processor($registry) {

        add_action(
            'nh_process_send',
            function ($channel, $payload) use ($registry) {

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("NH_Queue: processing send for {$channel}");
                }

                // Get Notifier service
                $notifier = $registry->get_svc('notifier');
                if (!$notifier) {
                    error_log('NH_Queue: notifier not found in registry');
                    return;
                }

                // Deliver now (email / telegram / slack ...)
                $notifier->send_now($channel, $payload);
            },
            10,
            2
        );
    }

    /**
     * Detect if environment is localhost (dev mode)
     */
    protected static function is_localhost(): bool {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $server = $_SERVER['SERVER_ADDR'] ?? '';

        if (in_array($host, ['localhost', '127.0.0.1'], true)) return true;
        if (str_ends_with($host, '.test') || str_contains($host, '.local')) return true;
        if (in_array($server, ['127.0.0.1', '::1'], true)) return true;

        return false;
    }
}
