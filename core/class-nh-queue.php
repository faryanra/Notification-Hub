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
        if (function_exists('as_enqueue_async_action')) {

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
            // Fallback: WP-Cron single event (runs once ~soon)
            wp_schedule_single_event(
                time() + 5,
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
}
