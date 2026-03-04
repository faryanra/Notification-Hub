<?php

namespace NotificationHub\Services;

/**
 * Queue service for async notification delivery.
 *
 * Matches legacy behavior:
 * - On localhost, may run immediately (caller-controlled).
 * - Uses Action Scheduler when available, falls back to WP-Cron.
 *
 * @since 1.7.2
 */
final class QueueService {
    /**
     * @var callable
     */
    private $immediateRunner;

    /**
     * @var callable
     */
    private $isLocalhost;

    /**
     * @param callable $immediateRunner function(string $channel, array $payload): void
     * @param callable $isLocalhost function(): bool
     */
    public function __construct(callable $immediateRunner, callable $isLocalhost) {
        $this->immediateRunner = $immediateRunner;
        $this->isLocalhost     = $isLocalhost;
    }

    /**
     * Enqueue or execute a send job.
     *
     * @since 1.7.2
     */
    public function enqueueSend(string $channel, array $payload, bool $runImmediatelyOnLocalhost = true): void {
        if ($runImmediatelyOnLocalhost && (bool) call_user_func($this->isLocalhost)) {
            call_user_func($this->immediateRunner, $channel, $payload);
            return;
        }

        // Prefer Action Scheduler when available.
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('nh_process_send', [
                'channel' => $channel,
                'payload' => $payload,
            ]);
            return;
        }

        // Fallback: WP-Cron.
        wp_schedule_single_event(time() + 3, 'nh_process_send', [
            'channel' => $channel,
            'payload' => $payload,
        ]);
    }
}
