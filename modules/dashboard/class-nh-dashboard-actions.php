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
            self::json_error(esc_html__('Access denied.', 'notification-hub'), 403);
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

        $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
        if (!$id) {
            self::json_error(esc_html__('Invalid ID.', 'notification-hub'), 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $deleted = $wpdb->delete($table, ['id' => $id], ['%d']);

        if ($wpdb->last_error) {
            self::json_error(esc_html__('Database error.', 'notification-hub'), 500);
        }

        if ($deleted === false || $deleted === 0) {
            self::json_error(esc_html__('Notification not found.', 'notification-hub'), 404);
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
            self::json_error(esc_html__('Access denied.', 'notification-hub'), 403);
        }

        $id    = isset($_REQUEST['id']) ? absint(wp_unslash($_REQUEST['id'])) : 0;
        $nonce = isset($_REQUEST['_wpnonce'])
            ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']))
            : (isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '');

        if (!$id || !wp_verify_nonce($nonce, 'nh_view_' . $id)) {
            self::json_error(esc_html__('Invalid request.', 'notification-hub'), 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));

        if (!$row) {
            self::json_error(esc_html__('Notification not found.', 'notification-hub'), 404);
        }

        // Normalize into a channel-ready payload so Preview === real channel output.
        $payload = [
            'title'   => (string) $row->title,
            'summary' => (string) $row->message,
            'source'  => (string) $row->source,
            'type'    => isset($row->type) ? (string) $row->type : '',
            'context' => !empty($row->context) ? json_decode((string) $row->context, true) : [],
            'link'    => isset($row->link) ? (string) $row->link : '',
            'no_log'  => true,
        ];

        if (!is_array($payload['context'])) {
            $payload['context'] = [];
        }

        $preview = [
            'email'    => class_exists('NH_Template') ? NH_Template::render_notification('email', $payload) : $payload['summary'],
            'telegram' => class_exists('NH_Template') ? NH_Template::render_notification('telegram', $payload) : $payload['summary'],
            'slack'    => class_exists('NH_Template') ? NH_Template::render_notification('slack', $payload) : $payload['summary'],
        ];

        wp_send_json_success([
            // Keep legacy keys for backwards compatibility with older JS.
            'title'      => (string) $row->title,
            'message'    => (string) $row->message,
            'source'     => (string) $row->source,
            'status'     => (int) $row->status,
            'created_at' => mysql2date('Y-m-d H:i', (string) $row->created_at),

            // New keys used by updated modal.
            'payload'    => $payload,
            'preview'    => $preview,
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
        $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
        if (!$id) {
            self::json_error(esc_html__('Invalid ID.', 'notification-hub'), 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // Build format for SET fields.
        $data_format = [];
        foreach ($data as $key => $value) {
            if ($key === 'read_at') {
                $data_format[] = is_null($value) ? '%s' : '%s';
            } elseif ($key === 'status') {
                $data_format[] = '%d';
            } else {
                $data_format[] = '%s';
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update($table, $data, ['id' => $id], $data_format, ['%d']);

        if ($wpdb->last_error) {
            self::json_error(esc_html__('Database error.', 'notification-hub'), 500);
        }

        if ($updated === false) {
            self::json_error(esc_html__('Update failed.', 'notification-hub'), 500);
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
            self::json_error(esc_html__('Access denied.', 'notification-hub'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
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

        $action  = isset($_POST['bulk_action']) ? sanitize_key((string) wp_unslash($_POST['bulk_action'])) : '';
        $ids_raw = isset($_POST['ids']) ? (array) wp_unslash($_POST['ids']) : [];
        $ids     = array_values(array_filter(array_map('absint', $ids_raw)));

        if ($action === '' || empty($ids)) {
            self::json_error(esc_html__('Invalid request.', 'notification-hub'), 400);
        }

        if (!class_exists('NH_Table_Bulk_Actions') || !method_exists('NH_Table_Bulk_Actions', 'process')) {
            self::json_error(esc_html__('Bulk actions module is not available.', 'notification-hub'), 500);
        }

        $result = NH_Table_Bulk_Actions::process($action, $ids);

        if ($result === false) {
            self::json_error(esc_html__('Bulk action failed.', 'notification-hub'), 500);
        }

        wp_send_json_success(['affected' => (int) $result]);
    }
}

add_action('admin_init', ['NH_Dashboard_Actions', 'init']);