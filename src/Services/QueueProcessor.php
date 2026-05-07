<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\QueueRepository;

/**
 * Queue processor for nh_process_send hook.
 *
 * @since 1.0.0
 */
final class QueueProcessor {
    /**
     * Retry delays in seconds.
     *
     * retry #1 => 60s, #2 => 300s, #3 => 900s
     *
     * @var array<int,int>
     */
    private const RETRY_DELAYS = [
        1 => 60,
        2 => 300,
        3 => 900,
    ];

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
     * Process due pending jobs (cron fallback).
     */
    public function process(int $limit = 20): void {
        $repo = new QueueRepository();
        $jobs = $repo->listDueJobs($limit);

        foreach ($jobs as $job) {
            $id = isset($job['id']) ? (int) $job['id'] : 0;
            if ($id > 0) {
                $this->processPersistedJob($id);
            }
        }
    }

    /**
     * @param mixed $jobOrChannel
     * @param mixed $payloadOrNull
     */
    public function handle($jobOrChannel, $payloadOrNull = null): void {
        if (is_numeric($jobOrChannel) && ($payloadOrNull === null || $payloadOrNull === '')) {
            $this->processPersistedJob((int) $jobOrChannel);
            return;
        }

        // Direct action fallback path.
        $channel = sanitize_key((string) $jobOrChannel);
        $payload = is_array($payloadOrNull) ? $payloadOrNull : [];
        if ($channel === '') {
            return;
        }

        $result = $this->dispatcher->sendNowDetailed($channel, $payload);
        if (!$result['ok']) {
            EventLogger::warn('queue', 'queue_direct_send_failed', 'Direct queue send failed', [
                'channel' => $channel,
                'retryable' => !empty($result['retryable']),
                'http_code' => isset($result['http_code']) ? (int) $result['http_code'] : 0,
            ]);
        }
    }

    private function processPersistedJob(int $jobId): void {
        $repo = new QueueRepository();
        $job = $repo->getJobById($jobId);

        if (!$job) {
            EventLogger::error('queue', 'queue_job_missing', 'Queue job not found', [
                'job_id' => $jobId,
            ]);
            return;
        }

        $status = sanitize_key((string) ($job['status'] ?? 'pending'));
        if (in_array($status, ['done', 'failed'], true)) {
            return;
        }

        $availableAtRaw = isset($job['available_at']) ? (string) $job['available_at'] : '';
        $availableAtTs = $availableAtRaw !== '' ? strtotime($availableAtRaw) : time();
        if ($availableAtTs === false) {
            $availableAtTs = time();
        }

        if ($availableAtTs > time()) {
            $delay = $availableAtTs - time();
            $this->scheduleJob($jobId, $delay);
            return;
        }

        $repo->markProcessing($jobId);

        $channel = sanitize_key((string) ($job['channel'] ?? ''));
        $payload = [];
        $payloadJson = isset($job['payload']) ? (string) $job['payload'] : '';
        if ($payloadJson !== '') {
            $decoded = json_decode($payloadJson, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        if ($channel === '') {
            $repo->markFailed($jobId, (int) ($job['attempts'] ?? 0), 'missing_channel');
            EventLogger::error('queue', 'queue_job_missing_channel', 'Queue job missing channel', [
                'job_id' => $jobId,
            ]);
            return;
        }

        $result = $this->dispatcher->sendNowDetailed($channel, $payload);
        if (!empty($result['ok'])) {
            $repo->markDone($jobId);
            EventLogger::info('queue', 'queue_job_done', 'Queue job delivered', [
                'job_id' => $jobId,
                'channel' => $channel,
            ]);
            return;
        }

        $attempts = (int) ($job['attempts'] ?? 0) + 1;
        $retryLimit = (int) ($job['retry_limit'] ?? 3);
        if ($retryLimit <= 0) {
            $retryLimit = 3;
        }

        $retryable = !empty($result['retryable']);
        $errorText = isset($result['error']) ? sanitize_text_field((string) $result['error']) : 'send_failed';

        if ($retryable && $attempts <= $retryLimit) {
            $delay = $this->retryDelay($attempts);
            $repo->scheduleRetry($jobId, $attempts, $delay, $errorText);
            $this->scheduleJob($jobId, $delay);

            EventLogger::warn('queue', 'queue_retry_scheduled', 'Queue retry scheduled', [
                'job_id' => $jobId,
                'channel' => $channel,
                'attempts' => $attempts,
                'retry_limit' => $retryLimit,
                'delay_seconds' => $delay,
                'http_code' => isset($result['http_code']) ? (int) $result['http_code'] : 0,
                'error' => $errorText,
            ]);
            return;
        }

        $repo->markFailed($jobId, $attempts, $errorText);
        EventLogger::error('queue', 'queue_job_failed', 'Queue job failed permanently', [
            'job_id' => $jobId,
            'channel' => $channel,
            'attempts' => $attempts,
            'retry_limit' => $retryLimit,
            'retryable' => $retryable,
            'http_code' => isset($result['http_code']) ? (int) $result['http_code'] : 0,
            'error' => $errorText,
        ]);
    }

    private function retryDelay(int $attempts): int {
        return self::RETRY_DELAYS[$attempts] ?? 900;
    }

    private function scheduleJob(int $jobId, int $delaySeconds): void {
        $delaySeconds = max(1, $delaySeconds);

        if (function_exists('as_schedule_single_action')) {
            as_schedule_single_action(time() + $delaySeconds, 'nh_process_send', [$jobId]);
            return;
        }

        wp_schedule_single_event(time() + $delaySeconds, 'nh_process_send', [$jobId]);
    }
}

