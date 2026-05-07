<?php
namespace NotificationHub\Repositories;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Metrics data access for notification analytics.
 *
 * @since 1.0.0
 */
final class MetricsRepository {
    /**
     * @var array<int,string>
     */
    private const HIDDEN_TYPES = ['dispatch_check', 'email_sent'];

    private function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'nh_notifications';
    }

    /**
     * Aggregate counts by day for a datetime range [from, toExclusive).
     *
     * @return array<int,array{date:string,count:int}>
     */
    public function countsByDay(string $fromMysql, string $toMysqlExclusive): array {
        global $wpdb;

        $table = $this->table();
        $hidden = array_map('sanitize_key', self::HIDDEN_TYPES);
        $hiddenPlaceholders = implode(', ', array_fill(0, count($hidden), '%s'));

        $sql = "SELECT DATE(created_at) AS day_date, COUNT(*) AS day_count
                FROM {$table} FORCE INDEX (idx_created_at)
                WHERE created_at >= %s
                  AND created_at < %s
                  AND type NOT IN ({$hiddenPlaceholders})
                GROUP BY DATE(created_at)
                ORDER BY day_date ASC";

        $params = array_merge([$fromMysql, $toMysqlExclusive], $hidden);

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $prepared = $wpdb->prepare($sql, ...$params);
        if (!is_string($prepared) || $prepared === '') {
            return [];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results($prepared, ARRAY_A);
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $date = isset($row['day_date']) ? (string) $row['day_date'] : '';
            if ($date === '') {
                continue;
            }

            $out[] = [
                'date'  => $date,
                'count' => isset($row['day_count']) ? (int) $row['day_count'] : 0,
            ];
        }

        return $out;
    }
}
