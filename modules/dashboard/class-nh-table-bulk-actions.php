<?php
/**
 * Table Bulk Actions Handler
 * 
 * @package Notification Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Table_Bulk_Actions {

    /**
     * Process bulk action
     */
    public static function process($action, $ids) {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $ids = array_map('intval', $ids);
        $in = implode(',', $ids);

        switch ($action) {
            case 'delete':
                return $wpdb->query("DELETE FROM {$table} WHERE id IN ({$in})");

            case 'archive':
                return $wpdb->query("UPDATE {$table} SET status = 1 WHERE id IN ({$in})");

            case 'unarchive':
                return $wpdb->query("UPDATE {$table} SET status = 0 WHERE id IN ({$in})");

            case 'nh_bulk_mark_read':
                return $wpdb->query("UPDATE {$table} SET read_at = NOW() WHERE id IN ({$in})");

            case 'nh_bulk_mark_unread':  // ✅ اضافه شد
                return $wpdb->query("UPDATE {$table} SET read_at = NULL WHERE id IN ({$in})");

            case 'mark_important':
                return $wpdb->query("UPDATE {$table} SET status = 3 WHERE id IN ({$in})");

            case 'unmark_important':
                return $wpdb->query("UPDATE {$table} SET status = 0 WHERE id IN ({$in}) AND status = 3");

            default:
                return false;
        }
    }
}
