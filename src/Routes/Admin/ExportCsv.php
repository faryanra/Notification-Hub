<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin-post: export notifications as CSV.
 *
 * @since 1.0.0
 */
final class ExportCsv {
    public function handle(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Forbidden', 'notification-hub'), 403);
        }

        check_admin_referer('nh_export_csv');

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $columns = $wpdb->get_col("DESCRIBE {$table}", 0);

        $wanted = [
            'id',
            'source',
            'type',
            'title',
            'message',
            'status',
            'priority',
            'tags',
            'context',
            'created_at',
            'updated_at',
            'read_at',
        ];

        $cols = array_values(array_intersect($wanted, is_array($columns) ? $columns : []));
        if (empty($cols)) {
            wp_die(esc_html__('No exportable columns found.', 'notification-hub'));
        }

        $cols_sql = implode(', ', array_map(static function ($c) {
            return preg_replace('/[^a-zA-Z0-9_]/', '', (string) $c);
        }, $cols));

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $rows = $wpdb->get_results("SELECT {$cols_sql} FROM {$table} ORDER BY created_at DESC", ARRAY_A);
        if (empty($rows) || !is_array($rows)) {
            wp_die(esc_html__('No notifications found to export.', 'notification-hub'));
        }

        $this->outputCsv($cols, $rows);
    }

    /**
     * @param array<int,string> $columns
     * @param array<int,array<string,mixed>> $rows
     */
    private function outputCsv(array $columns, array $rows): void {
        if (headers_sent()) {
            wp_die(esc_html__('Cannot export CSV because headers were already sent.', 'notification-hub'));
        }

        $filename = 'notification-hub-export-' . gmdate('Y-m-d') . '.csv';

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

        fputcsv($out, $columns);

        foreach ($rows as $row) {
            $row = $this->formatRow(is_array($row) ? $row : []);

            $ordered = [];
            foreach ($columns as $col) {
                $ordered[] = $row[$col] ?? '';
            }

            fputcsv($out, $ordered);
        }

        fclose($out);
        exit;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function formatRow(array $row): array {
        if (isset($row['tags']) && is_string($row['tags']) && $row['tags'] !== '') {
            $decoded = json_decode($row['tags'], true);
            if (is_array($decoded)) {
                $row['tags'] = implode('|', array_map('strval', $decoded));
            }
        } elseif (array_key_exists('tags', $row) && $row['tags'] === null) {
            $row['tags'] = '';
        }

        if (isset($row['context']) && is_string($row['context']) && $row['context'] !== '') {
            $decoded = json_decode($row['context'], true);
            if (is_array($decoded)) {
                $row['context'] = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        } elseif (array_key_exists('context', $row) && $row['context'] === null) {
            $row['context'] = '';
        }

        return $row;
    }
}

