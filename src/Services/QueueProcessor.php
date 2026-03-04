<?php

namespace NotificationHub\Services;

/**
 * Queue processor for nh_process_send hook.
 *
 * @since 1.7.2
 */
final class QueueProcessor {
    /**
     * @var NotificationDispatcher
     */
    private $dispatcher;

    public function __construct(NotificationDispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Hook WordPress action that processes queued delivery jobs.
     */
    public function register(): void {
        add_action('nh_process_send', [$this, 'handle'], 10, 2);
    }

    /**
     * @param string $channel
     * @param array<string,mixed> $payload
     */
    public function handle($channel, $payload): void {
        $this->dispatcher->sendNow((string) $channel, (array) $payload);
    }
}
