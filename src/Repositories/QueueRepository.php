<?php
namespace NotificationHub\Repositories;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Queue settings + jobs repository.
 *
 * @since 1.0.0
 */
final class QueueRepository {
    private const OPTION = 'nh_queue_settings';

    private function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'nh_queue';
    }

    /**
     * @return array{localhost_immediate: bool}
     */
    public function get(): array {
        $defaults = [
            'localhost_immediate' => true,
        ];

        $opt = get_option(self::OPTION, []);
        $opt = is_array($opt) ? $opt : [];

        return [
            'localhost_immediate' => array_key_exists('localhost_immediate', $opt)
                ? (bool) $opt['localhost_immediate']
                : (bool) $defaults['localhost_immediate'],
        ];
    }

    /**
     * @param array{localhost_immediate?: bool} $settings
     */
    public function update(array $settings): void {
        $current = $this->get();

        if (array_key_exists('localhost_immediate', $settings)) {
            $current['localhost_immediate'] = (bool) $settings['localhost_immediate'];
        }

        update_option(self::OPTION, $current);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function createJob(string $channel, array $payload, int $retryLimit = 3): int {
        global $wpdb;

        $channel = sanitize_key($channel);
        if ($channel === '') {
            return 0;
        }

        $retryLimit = max(1, $retryLimit);
        $json = wp_json_encode($payload);
        if (!is_string($json) || $json === '') {
            $json = '{}';
        }

        $now = gmdate('Y-m-d H:i:s');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
            $this->table(),
            [
                'channel'     => $channel,
                'payload'     => $json,
                'status'      => 'pending',
                'attempts'    => 0,
                'retry_limit' => $retryLimit,
                'available_at'=> $now,
                'last_error'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            ['%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getJobById(int $id): ?array {
        global $wpdb;

        $id = absint($id);
        if ($id <= 0) {
            return null;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $this->table() . ' WHERE id=%d', $id), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function markProcessing(int $id): bool {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'status' => 'processing',
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function markDone(int $id): bool {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'status' => 'done',
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function deferUntil(int $id, int $timestamp): bool {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        $when = gmdate('Y-m-d H:i:s', max(time(), $timestamp));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'status' => 'pending',
                'available_at' => $when,
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function scheduleRetry(int $id, int $attempts, int $delaySeconds, string $lastError = ''): bool {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        $attempts = max(0, (int) $attempts);
        $when = gmdate('Y-m-d H:i:s', time() + max(1, $delaySeconds));

        $lastError = sanitize_text_field($lastError);
        if (strlen($lastError) > 1000) {
            $lastError = substr($lastError, 0, 1000);
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'status' => 'pending',
                'attempts' => $attempts,
                'available_at' => $when,
                'last_error' => $lastError,
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $id],
            ['%s', '%d', '%s', '%s', '%s'],
            ['%d']
        );

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function markFailed(int $id, int $attempts, string $lastError = ''): bool {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        $attempts = max(0, (int) $attempts);
        $lastError = sanitize_text_field($lastError);
        if (strlen($lastError) > 1000) {
            $lastError = substr($lastError, 0, 1000);
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'status' => 'failed',
                'attempts' => $attempts,
                'last_error' => $lastError,
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $id],
            ['%s', '%d', '%s', '%s'],
            ['%d']
        );

        return ($updated !== false) && empty($wpdb->last_error);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listDueJobs(int $limit = 20): array {
        global $wpdb;

        $limit = max(1, min(200, $limit));
        $now = gmdate('Y-m-d H:i:s');

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $sql = $wpdb->prepare(
            'SELECT * FROM ' . $this->table() . " WHERE status='pending' AND (available_at IS NULL OR available_at<=%s) ORDER BY id ASC LIMIT %d",
            $now,
            $limit
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results($sql, ARRAY_A);
        return is_array($rows) ? $rows : [];
    }
}

