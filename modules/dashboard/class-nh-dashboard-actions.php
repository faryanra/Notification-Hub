<?php
/**
 * Dashboard AJAX Actions
 * 
 * Handles AJAX requests for notifications (view, mark read, delete).
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Dashboard_Actions {

    /**
     * Register AJAX handlers
     */
    public static function init() {
        // Admin POST
        add_action('admin_post_nh_delete_notification', [__CLASS__, 'delete_notification']);

        // AJAX
        add_action('wp_ajax_nh_view_notification', [__CLASS__, 'view_notification']);
        add_action('wp_ajax_nh_mark_read', [__CLASS__, 'mark_read']);
        add_action('wp_ajax_nh_mark_unread', [__CLASS__, 'mark_unread']);
        add_action('wp_ajax_nh_mark_important', [__CLASS__, 'mark_important']);
        add_action('wp_ajax_nh_unmark_important', [__CLASS__, 'unmark_important']);
        add_action('wp_ajax_nh_delete_notification', [__CLASS__, 'delete_notification_ajax']);
        add_action('wp_ajax_nh_get_unread_count', [__CLASS__, 'get_unread_count']);
        add_action('wp_ajax_nh_bulk_action', [__CLASS__, 'bulk_action_ajax']);
    }

    /**
     * Verify user permissions and nonce
     */
    private static function ensure_access() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Access denied', 'notification-hub')], 403);
        }
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');
    }

    /**
     * Delete notification (non-AJAX)
     */
    public static function delete_notification() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied', 'notification-hub'));
        }

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'nh_delete_' . $id)) {
            wp_die(__('Invalid request', 'notification-hub'));
        }

        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'nh_notifications', ['id' => $id]);

        wp_redirect(admin_url('admin.php?page=nh-dashboard'));
        exit;
    }

    /**
     * Delete notification (AJAX)
     */
    public static function delete_notification_ajax() {
        self::ensure_access();
        
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid ID', 'notification-hub')], 400);
        }

        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'nh_notifications', ['id' => $id], ['%d']);

        if ($wpdb->last_error) {
            wp_send_json_error(['message' => $wpdb->last_error], 500);
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * View notification details (AJAX)
     */
    public static function view_notification() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Access denied', 'notification-hub')], 403);
        }

        $id = (int) ($_REQUEST['id'] ?? 0);
        $nonce = $_REQUEST['_wpnonce'] ?? $_REQUEST['nonce'] ?? '';

        if (!$id || !wp_verify_nonce($nonce, 'nh_view_' . $id)) {
            wp_send_json_error(['message' => __('Invalid request', 'notification-hub')], 400);
        }

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nh_notifications WHERE id=%d",
            $id
        ));

        if (!$row) {
            wp_send_json_error(['message' => __('Notification not found', 'notification-hub')], 404);
        }

        wp_send_json_success([
            'title'      => $row->title,
            'message'    => $row->message,
            'source'     => $row->source,
            'status'     => $row->status,
            'created_at' => mysql2date('Y-m-d H:i', $row->created_at),
        ]);
    }

    /**
     * Mark notification as read
     */
    public static function mark_read() {
        self::ensure_access();
        self::update(['read_at' => current_time('mysql')]);
    }

    /**
     * Mark notification as unread
     */
    public static function mark_unread() {
        self::ensure_access();
        self::update(['read_at' => null]);
    }

    /**
     * Mark notification as important
     */
    public static function mark_important() {
        self::ensure_access();
        self::update(['status' => 3]);
    }

    /**
     * Remove important flag
     */
    public static function unmark_important() {
        self::ensure_access();
        self::update(['status' => 0]);
    }

    /**
     * Update notification in database
     */
    private static function update(array $data) {
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid ID', 'notification-hub')], 400);
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'nh_notifications',
            $data,
            ['id' => $id]
        );

        if ($wpdb->last_error) {
            wp_send_json_error(['message' => $wpdb->last_error], 500);
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * Get unread notification count
     */
    public static function get_unread_count() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Access denied', 'notification-hub')], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} WHERE status IN (0,3) AND read_at IS NULL"
        );

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Process bulk actions via AJAX
     */
    public static function bulk_action_ajax() {
        self::ensure_access();
        
        $action = sanitize_key($_POST['bulk_action'] ?? '');
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        
        if (empty($action) || empty($ids)) {
            wp_send_json_error(['message' => __('Invalid request', 'notification-hub')], 400);
        }
        
        $result = NH_Table_Bulk_Actions::process($action, $ids);
        
        if ($result === false) {
            wp_send_json_error(['message' => __('Bulk action failed', 'notification-hub')], 500);
        }
        
        wp_send_json_success(['affected' => $result]);
    }
}

add_action('admin_init', ['NH_Dashboard_Actions', 'init']);
