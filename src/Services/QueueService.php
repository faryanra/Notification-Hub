<?php

namespace NotificationHub\Services;

use NotificationHub\Repositories\QueueRepository;

/**
 * Queue service for async notification delivery.
 *
 * Persists jobs in nh_queue and schedules processing hook.
 *
 * @since 1.7.3
 */
final class QueueService {
    /**
     * @var callable
     */
    private $isLocalhost;

    /**
     * @param callable $isLocalhost function(): bool
     */
    public function __construct(callable $isLocalhost) {
        $this->isLocalhost = $isLocalhost;
    }

    /**
     * Enqueue a send job and schedule queue processing.
     *
     * @since 1.7.3
     * @param array<string,mixed> $payload
     */
    public function enqueueSend(string $channel, array $payload, bool $runImmediatelyOnLocalhost = true): void {
        $channel = sanitize_key($channel);
        if ($channel === '') {
            return;
        }

        $repo = new QueueRepository();
        $jobId = $repo->createJob($channel, $payload, 3);
        if ($jobId <= 0) {
            EventLogger::error('queue', 'queue_job_create_failed', 'Failed to create queue job', [
                'channel' => $channel,
            ]);
            return;
        }

        EventLogger::info('queue', 'queue_job_enqueued', 'Queue job enqueued', [
            'job_id' => $jobId,
            'channel' => $channel,
        ]);

        $settings = $repo->get();
        $localhostImmediate = $runImmediatelyOnLocalhost
            && (bool) call_user_func($this->isLocalhost)
            && !empty($settings['localhost_immediate']);

        if ($localhostImmediate) {
            do_action('nh_process_send', $jobId);
            return;
        }

        $this->scheduleJob($jobId, 3);
    }

    private function scheduleJob(int $jobId, int $delaySeconds): void {
        $delaySeconds = max(0, $delaySeconds);

        if (function_exists('as_schedule_single_action')) {
            as_schedule_single_action(time() + $delaySeconds, 'nh_process_send', [$jobId]);
            return;
        }

        if (function_exists('as_enqueue_async_action') && $delaySeconds === 0) {
            as_enqueue_async_action('nh_process_send', [$jobId]);
            return;
        }

        wp_schedule_single_event(time() + max(1, $delaySeconds), 'nh_process_send', [$jobId]);
    }
}
