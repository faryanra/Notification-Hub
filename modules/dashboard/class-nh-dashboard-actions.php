<?php
/**
 * NH_Dashboard_Actions
 *
 * Handles AJAX requests for notifications (view, mark read/unread, important/unimportant, delete, bulk actions).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Dashboard_Actions {

    /**
     * Register AJAX handlers.
     *
     * @since 1.6.2
     * @return void
     */
    public static function init(): void {
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
     * Verify user permissions and AJAX nonce.
     *
     * @since 1.6.2
     * @return void
     */
    private static function ensure_access(): void {
        if (!current_user_can('manage_options')) {
            self::json_error(__('Access denied.', 'notification-hub'), 403);
        }

        check_ajax_referer('nh_ajax_nonce', '_wpnonce');
    }

    /**
     * Send a standardized JSON error response and exit.
     *
     * @since 1.6.2
     * @param string $message Error message.
     * @param int    $status HTTP status code.
     * @return void
     */
    private static function json_error(string $message, int $status): void {
        wp_send_json_error(['message' => $message], $status);
    }

    /**
     * Delete notification (AJAX).
     *
     * @since 1.6.2
     * @return void
     */
    public static function delete_notification_ajax(): void {
        self::ensure_access();

        $id = (int) (wp_unslash($_POST['id'] ?? 0));
        if (!$id) {
            self::json_error(__('Invalid ID.', 'notification-hub'), 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $deleted = $wpdb->delete($table, ['id' => $id], ['%d']);

        if ($wpdb->last_error) {
            self::json_error(__('Database error.', 'notification-hub'), 500);
        }

        if ($deleted === false || $deleted === 0) {
            self::json_error(__('Notification not found.', 'notification-hub'), 404);
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * View notification details (AJAX).
     *
     * Note: uses per-row nonce (nh_view_{id}) because it’s a read action tied to a row.
     *
     * @since 1.6.2
     * @return void
     */
    public static function view_notification(): void {
        if (!current_user_can('manage_options')) {
            self::json_error(__('Access denied.', 'notification-hub'), 403);
        }

        $id = (int) (wp_unslash($_REQUEST['id'] ?? 0));
        $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? ($_REQUEST['nonce'] ?? '')));

        if (!$id || !wp_verify_nonce($nonce, 'nh_view_' . $id)) {
            self::json_error(__('Invalid request.', 'notification-hub'), 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id)
        );

        if (!$row) {
            self::json_error(__('Notification not found.', 'notification-hub'), 404);
        }

        wp_send_json_success([
            'title'      => (string) $row->title,
            'message'    => (string) $row->message,
            'source'     => (string) $row->source,
            'status'     => (int) $row->status,
            'created_at' => mysql2date('Y-m-d H:i', $row->created_at),
        ]);
    }

    /**
     * Mark notification as read (AJAX).
     *
     * @since 1.6.2
     * @return void
     */
    public static function mark_read(): void {
        self::ensure_access();
        self::update(['read_at' => current_time('mysql')]);
    }

    /**
     * Mark notification as unread (AJAX).
     *
     * @since 1.6.2
     * @return void
     */
    public static function mark_unread(): void {
        self::ensure_access();
        self::update(['read_at' => null]);
    }

    /**
     * Mark notification as important (AJAX).
     *
     * @since 1.6.2
     * @return void
     */
    public static function mark_important(): void {
        self::ensure_access();
        self::update(['status' => 3]);
    }

    /**
     * Remove important flag (AJAX).
     *
     * @since 1.6.2
     * @return void
     */
    public static function unmark_important(): void {
        self::ensure_access();
        self::update(['status' => 0]);
    }

    /**
     * Update notification in database (AJAX).
     *
     * @since 1.6.2
     * @param array $data Data to update.
     * @return void
     */
    private static function update(array $data): void {
        $id = (int) (wp_unslash($_POST['id'] ?? 0));
        if (!$id) {
            self::json_error(__('Invalid ID.', 'notification-hub'), 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $updated = $wpdb->update($table, $data, ['id' => $id], null, ['%d']);

        if ($wpdb->last_error) {
            self::json_error(__('Database error.', 'notification-hub'), 500);
        }

        if ($updated === false) {
            self::json_error(__('Update failed.', 'notification-hub'), 500);
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * Get unread notification count (AJAX).
     *
     * @since 1.6.2
     * @return void
     */
    public static function get_unread_count(): void {
        if (!current_user_can('manage_options')) {
            self::json_error(__('Access denied.', 'notification-hub'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} WHERE status IN (0,3) AND read_at IS NULL"
        );

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Process bulk actions via AJAX.
     *
     * @since 1.6.2
     * @return void
     */
    public static function bulk_action_ajax(): void {
        self::ensure_access();

        $action = sanitize_key((string) (wp_unslash($_POST['bulk_action'] ?? '')));
        $ids_raw = (array) (wp_unslash($_POST['ids'] ?? []));
        $ids = array_values(array_filter(array_map('intval', $ids_raw)));

        if ($action === '' || empty($ids)) {
            self::json_error(__('Invalid request.', 'notification-hub'), 400);
        }

        if (!class_exists('NH_Table_Bulk_Actions') || !method_exists('NH_Table_Bulk_Actions', 'process')) {
            self::json_error(__('Bulk actions module is not available.', 'notification-hub'), 500);
        }

        $result = NH_Table_Bulk_Actions::process($action, $ids);

        if ($result === false) {
            self::json_error(__('Bulk action failed.', 'notification-hub'), 500);
        }

        wp_send_json_success(['affected' => (int) $result]);
    }
}

add_action('admin_init', ['NH_Dashboard_Actions', 'init']);
