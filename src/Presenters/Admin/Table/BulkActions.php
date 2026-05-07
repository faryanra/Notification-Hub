<?php
/**
 * NH_Table_Bulk_Actions
 *
 * Table bulk actions handler for notifications.
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Table_Bulk_Actions {

    /**
     * Process a bulk action.
     *
     * Returns number of affected rows, or false on failure.
     *
     * @since 1.0.0
     * @param string $action Bulk action key.
     * @param array  $ids List of notification IDs.
     * @return int|false
     */
    public static function process($action, $ids) {
        $action = sanitize_key((string) $action);

        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return false;
        }

        $allowed_actions = [
            'delete',
            'archive',
            'unarchive',
            'nh_bulk_mark_read',
            'nh_bulk_mark_unread',
            'mark_important',
            'unmark_important',
        ];
        if (!in_array($action, $allowed_actions, true)) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        $prepare_args = $ids;

        switch ($action) {
            case 'delete':
                $query = "DELETE FROM {$table} WHERE id IN ({$placeholders})";
                break;

            case 'archive':
                $query = "UPDATE {$table} SET status = 1 WHERE id IN ({$placeholders})";
                break;

            case 'unarchive':
                $query = "UPDATE {$table} SET status = 0 WHERE id IN ({$placeholders})";
                break;

            case 'nh_bulk_mark_read':
                $now          = current_time('mysql');
                $prepare_args = array_merge([$now], $ids);
                $query        = "UPDATE {$table} SET read_at = %s WHERE id IN ({$placeholders})";
                break;

            case 'nh_bulk_mark_unread':
                $query = "UPDATE {$table} SET read_at = NULL WHERE id IN ({$placeholders})";
                break;

            case 'mark_important':
                $query = "UPDATE {$table} SET status = 3 WHERE id IN ({$placeholders})";
                break;

            case 'unmark_important':
                $query = "UPDATE {$table} SET status = 0 WHERE status = 3 AND id IN ({$placeholders})";
                break;

            default:
                return false;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $sql = $wpdb->prepare($query, $prepare_args);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        return (int) $wpdb->query($sql);
    }
}

