<?php
/**
 * NH_Admin_CSV_Export
 *
 * Export notifications to CSV.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Admin_CSV_Export {

    /**
     * Register export action handler.
     *
     * @since 1.6.2
     * @return void
     */
    public static function init(): void {
        add_action('admin_post_nh_export_csv', [__CLASS__, 'export']);
    }

    /**
     * Export notifications to CSV.
     *
     * @since 1.6.2
     * @return void
     */
    public static function export(): void {
        if (!class_exists('NH_Security')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_export_csv');

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // Read real columns from table to prevent selecting missing cols.
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

        // Build select statement with sanitized column names (from DESCRIBE output).
        $cols_sql = implode(', ', array_map(static function ($c) {
            return preg_replace('/[^a-zA-Z0-9_]/', '', (string) $c);
        }, $cols));

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $rows = $wpdb->get_results("SELECT {$cols_sql} FROM {$table} ORDER BY created_at DESC", ARRAY_A);

        if (empty($rows) || !is_array($rows)) {
            wp_die(esc_html__('No notifications found to export.', 'notification-hub'));
        }

        self::output_csv($cols, $rows);
    }

    /**
     * Output CSV headers and data.
     *
     * @since 1.6.2
     * @param array $columns Columns.
     * @param array $rows Rows.
     * @return void
     */
    private static function output_csv(array $columns, array $rows): void {
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

        // Add UTF-8 BOM for Excel compatibility (especially with Persian/Unicode).
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo "\xEF\xBB\xBF";

        fputcsv($out, $columns);

        foreach ($rows as $row) {
            $row = self::format_row(is_array($row) ? $row : []);

            // Keep column order stable.
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
     * Format row data for CSV output.
     *
     * @since 1.6.2
     * @param array $row Row.
     * @return array
     */
    private static function format_row(array $row): array {
        // tags: JSON array => a|b|c
        if (isset($row['tags']) && is_string($row['tags']) && $row['tags'] !== '') {
            $decoded = json_decode($row['tags'], true);
            if (is_array($decoded)) {
                $row['tags'] = implode('|', array_map('strval', $decoded));
            }
        } elseif (isset($row['tags']) && $row['tags'] === null) {
            $row['tags'] = '';
        }

        // context: normalize JSON to unicode-safe single-line json.
        if (isset($row['context']) && is_string($row['context']) && $row['context'] !== '') {
            $decoded = json_decode($row['context'], true);
            if (is_array($decoded)) {
                $row['context'] = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        } elseif (isset($row['context']) && $row['context'] === null) {
            $row['context'] = '';
        }

        return $row;
    }
}