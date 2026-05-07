<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use NotificationHub\Security\Capabilities;
use NotificationHub\Services\MetricsService;

/**
 * Admin-post: export analytics metrics as CSV.
 *
 * @since 1.0.0
 */
final class ExportMetricsCsv {
    public function handle(): void {
        Capabilities::ensureManageOptions();
        check_admin_referer('nh_export_metrics');

        $range = isset($_GET['range']) ? sanitize_key(wp_unslash($_GET['range'])) : '7d';
        if (!in_array($range, ['7d', '30d'], true)) {
            wp_die(esc_html__('Invalid range.', 'notification-hub'), 400);
        }

        $service = new MetricsService();
        try {
            $metrics = $service->getCountsByDay($range);
        } catch (InvalidArgumentException $e) {
            wp_die(esc_html__('Invalid range.', 'notification-hub'), 400);
        }

        $days = isset($metrics['days']) && is_array($metrics['days']) ? $metrics['days'] : [];
        $this->outputCsv($range, $days);
    }

    /**
     * @param array<int,array{date:string,count:int}> $days
     */
    private function outputCsv(string $range, array $days): void {
        if (headers_sent()) {
            wp_die(esc_html__('Cannot export CSV because headers were already sent.', 'notification-hub'));
        }

        $filename = 'notification-hub-metrics-' . $range . '-' . gmdate('Y-m-d') . '.csv';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        header('Content-Type: text/csv; charset=utf-8');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        header('Content-Disposition: attachment; filename=' . $filename);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        header('Pragma: no-cache');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        if (!$out) {
            wp_die(esc_html__('Unable to open output stream.', 'notification-hub'));
        }

        // Excel-friendly UTF-8 BOM.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo "\xEF\xBB\xBF";

        fputcsv($out, ['date', 'count']);
        foreach ($days as $row) {
            fputcsv($out, [(string) ($row['date'] ?? ''), (int) ($row['count'] ?? 0)]);
        }

        fclose($out);
        exit;
    }
}
