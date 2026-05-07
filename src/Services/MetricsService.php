<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use NotificationHub\Repositories\MetricsRepository;

/**
 * Analytics metrics service (counts by day).
 *
 * @since 1.0.0
 */
final class MetricsService {
    /**
     * @var array<string,int>
     */
    private const RANGE_DAYS = [
        '7d'  => 7,
        '30d' => 30,
    ];

    private MetricsRepository $repo;

    public function __construct(?MetricsRepository $repo = null) {
        $this->repo = $repo ?: new MetricsRepository();
    }

    /**
     * Build metrics payload for REST/UI/CSV.
     *
     * @return array{
     *   range:string,
     *   timezone:string,
     *   from:string,
     *   to:string,
     *   total:int,
     *   days:array<int,array{date:string,count:int}>
     * }
     */
    public function getCountsByDay(string $range): array {
        $range = sanitize_key($range);
        if (!isset(self::RANGE_DAYS[$range])) {
            throw new InvalidArgumentException('invalid_range');
        }

        $days = self::RANGE_DAYS[$range];
        $tz = wp_timezone();
        $today = new \DateTimeImmutable('now', $tz);

        $fromDate = $today->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
        $toDate = $today->format('Y-m-d');

        $fromMysql = $fromDate . ' 00:00:00';
        $toExclusiveMysql = $today->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $rows = $this->repo->countsByDay($fromMysql, $toExclusiveMysql);
        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['date']] = (int) $row['count'];
        }

        $filledDays = [];
        $total = 0;
        for ($i = 0; $i < $days; $i++) {
            $day = $today->modify('-' . (($days - 1) - $i) . ' days')->format('Y-m-d');
            $count = (int) ($map[$day] ?? 0);
            $filledDays[] = [
                'date'  => $day,
                'count' => $count,
            ];
            $total += $count;
        }

        $timezone = wp_timezone_string();
        if ($timezone === '') {
            $timezone = $tz->getName();
        }

        return [
            'range'    => $range,
            'timezone' => $timezone,
            'from'     => $fromDate,
            'to'       => $toDate,
            'total'    => $total,
            'days'     => $filledDays,
        ];
    }
}
