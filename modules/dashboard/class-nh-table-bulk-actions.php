<?php
/**
 * NH_Table_Bulk_Actions
 *
 * Table bulk actions handler for notifications.
 *
 * @package Notification_Hub
 * @since 1.6.2
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
     * @since 1.6.2
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

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // Build "IN (%d,%d,...)" safely.
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));

        switch ($action) {
            case 'delete': {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("DELETE FROM {$table} WHERE id IN ($placeholders)", $ids);
                return (int) $wpdb->query($sql);
            }

            case 'archive': {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("UPDATE {$table} SET status = 1 WHERE id IN ($placeholders)", $ids);
                return (int) $wpdb->query($sql);
            }

            case 'unarchive': {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("UPDATE {$table} SET status = 0 WHERE id IN ($placeholders)", $ids);
                return (int) $wpdb->query($sql);
            }

            case 'nh_bulk_mark_read': {
                $now = current_time('mysql');
                $args = array_merge([$now], $ids);
                $placeholders2 = '%s,' . $placeholders;

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("UPDATE {$table} SET read_at = %s WHERE id IN ($placeholders)", $args);
                return (int) $wpdb->query($sql);
            }

            case 'nh_bulk_mark_unread': {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("UPDATE {$table} SET read_at = NULL WHERE id IN ($placeholders)", $ids);
                return (int) $wpdb->query($sql);
            }

            case 'mark_important': {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("UPDATE {$table} SET status = 3 WHERE id IN ($placeholders)", $ids);
                return (int) $wpdb->query($sql);
            }

            case 'unmark_important': {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = $wpdb->prepare("UPDATE {$table} SET status = 0 WHERE status = 3 AND id IN ($placeholders)", $ids);
                return (int) $wpdb->query($sql);
            }

            default:
                return false;
        }
    }
}
