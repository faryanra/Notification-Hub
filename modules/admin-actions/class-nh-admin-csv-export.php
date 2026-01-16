<?php
/**
 * CSV Export Handler
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Admin_CSV_Export {

    /**
     * Register export action handler
     */
    public static function init() {
        add_action('admin_post_nh_export_csv', [__CLASS__, 'export']);
    }

    /**
     * Export notifications to CSV
     */
    public static function export() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied.', 'notification-hub'));
        }

        check_admin_referer('nh_export_csv');

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $columns = $wpdb->get_col("DESC {$table}", 0);
        $wanted = ['id', 'source', 'type', 'title', 'message', 'status', 'priority', 'tags', 'context', 'created_at', 'updated_at', 'read_at'];
        $cols = array_values(array_intersect($wanted, $columns));

        if (empty($cols)) {
            wp_die(__('No exportable columns found.', 'notification-hub'));
        }

        $cols_sql = implode(', ', $cols);
        $rows = $wpdb->get_results("SELECT {$cols_sql} FROM {$table} ORDER BY created_at DESC", ARRAY_A);

        if (empty($rows)) {
            wp_die(__('No notifications found to export.', 'notification-hub'));
        }

        self::output_csv($cols, $rows);
    }

    /**
     * Output CSV headers and data
     */
    private static function output_csv($columns, $rows) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=notification-hub-export.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, $columns);

        foreach ($rows as $row) {
            $row = self::format_row($row);
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    /**
     * Format row data for CSV output
     */
    private static function format_row($row) {
        if (isset($row['tags'])) {
            $row['tags'] = $row['tags'] ? implode('|', (array) json_decode($row['tags'], true)) : '';
        }

        if (isset($row['context']) && is_string($row['context']) && str_starts_with(trim($row['context']), '{')) {
            $row['context'] = json_encode(json_decode($row['context'], true), JSON_UNESCAPED_UNICODE);
        }

        return $row;
    }
}
