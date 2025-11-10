<?php
// =====================================================
// Notification Hub — Admin Actions / Hook CRUD / CSV Export
// =====================================================

if (!defined('ABSPATH')) exit;

/**
 * NH_Admin_Actions
 * Handles admin-side actions:
 * - Hook CRUD operations
 * - Test send for channels
 * - License save/revoke
 * - Notification actions (delete, archive, mark read)
 * - CSV export
 */
class NH_Admin_Actions {

    /**
     * Register admin_post action handlers
     */
    public static function init() {
        add_action('admin_post_nh_test_channel', [__CLASS__, 'handle_test_channel']);
        add_action('admin_post_nh_test_hook', [__CLASS__, 'handle_test_hook']);
        add_action('admin_post_nh_save_hook', [__CLASS__, 'handle_save_hook']);
        add_action('admin_post_nh_update_hook', [__CLASS__, 'handle_update_hook']);
        add_action('admin_post_nh_delete_hook', [__CLASS__, 'handle_delete_hook']);
    }

    /**
     * Redirect back to referrer with query args
     */
    private static function redirect_with($base_args) {
        $ref = wp_get_referer() ?: admin_url('admin.php');
        $url = add_query_arg($base_args, $ref);
        wp_safe_redirect($url);
        exit;
    }

    /**
     * Get current settings tab
     */
    private static function current_tab() {
        return isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    }

    // =====================================================
    // LICENSE MANAGEMENT
    // =====================================================

    public static function handle_save_license() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied', 'notification-hub'));
        }

        check_admin_referer('nh_save_license');
        $raw_key = sanitize_text_field($_POST['nh_license_key'] ?? '');

        NH_License::save_key($raw_key);
        NH_License::set_valid(!empty($raw_key));

        $url = add_query_arg([
            'page' => 'notification-hub-settings',
            'nh_license_saved' => 1,
        ], admin_url('admin.php'));

        wp_safe_redirect($url);
        exit;
    }

    public static function save_license() {
        if (!current_user_can('manage_options')) wp_die('Access denied');
        check_admin_referer('nh_save_license');

        $key = sanitize_text_field($_POST['nh_license_key'] ?? '');
        NH_License::save_key($key);
        NH_License::set_valid(!empty($key));

        $url = add_query_arg([
            'page' => 'nh_settings',
            'nh_license_saved' => 1,
        ], admin_url('admin.php'));

        wp_safe_redirect($url);
        exit;
    }

    public static function revoke_license() {
        if (!current_user_can('manage_options')) wp_die('Access denied');
        check_admin_referer('nh_license_revoke');
        NH_License::revoke();
        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_revoked=1'));
        exit;
    }

    // =====================================================
    // CHANNEL & HOOK TESTS
    // =====================================================

    /**
     * Test send for a specific channel (email/slack/telegram)
     */
    public static function handle_test_channel() {
        try {
            NH_Security::ensure_cap();
            NH_Security::verify_nonce('nh_test_channel');

            $channel  = sanitize_text_field($_GET['channel'] ?? '');
            $tab      = self::current_tab();

            $registry = NH_Core_Registry::get();
            $notifier = $registry->get_svc('notifier');
            if (!$notifier) wp_die(__('Notifier not found', 'notification-hub'));

            $ok = $notifier->send($channel, [
                'subject' => '🔔 Notification Hub Test',
                'message' => 'This is a test message from Notification Hub.',
            ]);

            $url = add_query_arg([
                'page'    => 'nh_settings',
                'tab'     => $tab,
                'nh_test' => $channel,
                'success' => $ok ? '1' : '0'
            ], admin_url('admin.php'));

            wp_safe_redirect($url);
            exit;

        } catch (Throwable $e) {
            if (WP_DEBUG) error_log('NH_Admin_Actions::handle_test_channel: ' . $e->getMessage());
            wp_die('Test failed: ' . esc_html($e->getMessage()));
        }
    }

    /**
     * Test trigger for a custom Hook
     */
    public static function handle_test_hook() {
        try {
            NH_Security::ensure_cap();

            $id = NH_Security::request_int('id');
            $nonce_ok = isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'nh_test_' . $id);
            if (!$id || !$nonce_ok) wp_die(__('Invalid nonce.', 'notification-hub'));

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));
            if (!$row) wp_die(__('Hook not found.', 'notification-hub'));

            $registry = NH_Core_Registry::get();
            $notifier = $registry->get_svc('notifier');
            if (!$notifier) wp_die(__('Notifier missing.', 'notification-hub'));

            $channels = json_decode($row->channels, true) ?: ['email'];
            foreach ($channels as $ch) {
                $notifier->send_now($ch, [
                    'title'  => '🔧 Test for ' . $row->title,
                    'body'   => 'Triggered manually via Notification Hub.',
                    'source' => 'hook',
                ]);
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_tested=1'));
            exit;

        } catch (Throwable $e) {
            if (WP_DEBUG) error_log('NH_Admin_Actions::handle_test_hook: ' . $e->getMessage());
            wp_die('Hook test failed: ' . esc_html($e->getMessage()));
        }
    }

    // =====================================================
    // HOOK CRUD (Add / Update / Delete)
    // =====================================================

    public static function handle_save_hook() {
        try {
            NH_Security::ensure_cap();
            NH_Security::verify_nonce('nh_save_hook');

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';

            $title   = sanitize_text_field($_POST['title'] ?? '');
            $action  = NH_Security::validate_action_name($_POST['action_name'] ?? '');
            $chs     = NH_Security::sanitize_channels($_POST['channels'] ?? []);
            $json    = wp_json_encode($chs);

            if ($title && $action) {
                $wpdb->insert($table, [
                    'title'       => $title,
                    'action_name' => $action,
                    'channels'    => $json,
                    'status'      => 1
                ], ['%s','%s','%s','%d']);
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_saved=1'));
            exit;

        } catch (Throwable $e) {
            if (WP_DEBUG) error_log('NH_Admin_Actions::handle_save_hook: ' . $e->getMessage());
            wp_die('Save failed: ' . esc_html($e->getMessage()));
        }
    }

    public static function handle_update_hook() {
        try {
            NH_Security::ensure_cap();
            $id = NH_Security::request_int('id');
            NH_Security::verify_nonce('nh_update_hook', $id);

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';

            $title  = sanitize_text_field($_POST['title'] ?? '');
            $action = NH_Security::validate_action_name($_POST['action_name'] ?? '');
            $chs    = NH_Security::sanitize_channels($_POST['channels'] ?? []);
            $json   = wp_json_encode($chs);

            if ($id > 0) {
                $wpdb->update(
                    $table,
                    ['title' => $title, 'action_name' => $action, 'channels' => $json],
                    ['id' => $id],
                    ['%s','%s','%s'],
                    ['%d']
                );
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_updated=1'));
            exit;

        } catch (Throwable $e) {
            if (WP_DEBUG) error_log('NH_Admin_Actions::handle_update_hook: ' . $e->getMessage());
            wp_die('Update failed: ' . esc_html($e->getMessage()));
        }
    }

    public static function handle_delete_hook() {
        try {
            NH_Security::ensure_cap();
            $id = NH_Security::request_int('id');
            NH_Security::verify_nonce('nh_delete_hook', $id);

            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'nh_hooks', ['id' => $id], ['%d']);

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_deleted=1'));
            exit;

        } catch (Throwable $e) {
            if (WP_DEBUG) error_log('NH_Admin_Actions::handle_delete_hook: ' . $e->getMessage());
            wp_die('Delete failed: ' . esc_html($e->getMessage()));
        }
    }

    // =====================================================
    // NOTIFICATION ACTIONS (Dashboard)
    // =====================================================

    public static function handle_delete() {
        if (!current_user_can('manage_options')) wp_die('Access denied');

        $id = intval($_GET['id'] ?? 0);
        if (!$id || !wp_verify_nonce($_GET['_wpnonce'], 'nh_delete_' . $id)) wp_die('Invalid nonce');

        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'nh_notifications', ['id' => $id], ['%d']);
        wp_redirect(admin_url('admin.php?page=nh-dashboard&deleted=1'));
        exit;
    }

    public static function handle_toggle() {
        if (!current_user_can('manage_options')) wp_die('Access denied');

        $id = intval($_GET['id'] ?? 0);
        $do = sanitize_text_field($_GET['do'] ?? '');
        if (!$id || !in_array($do, ['archive','unarchive'])) wp_die('Invalid action');
        if (!wp_verify_nonce($_GET['_wpnonce'], 'nh_toggle_' . $id)) wp_die('Invalid nonce');

        $status = ($do === 'archive') ? 1 : 0;
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'nh_notifications', ['status' => $status], ['id' => $id], ['%d'], ['%d']);

        wp_redirect(admin_url('admin.php?page=nh-dashboard&status_changed=1'));
        exit;
    }
}

// =====================================================
// Register Handlers
// =====================================================
add_action('admin_init', ['NH_Admin_Actions', 'init']);
add_action('admin_post_nh_delete_notification', ['NH_Admin_Actions', 'handle_delete']);
add_action('admin_post_nh_toggle_archive', ['NH_Admin_Actions', 'handle_toggle']);
add_action('admin_post_nh_save_license', ['NH_Admin_Actions', 'save_license']);
add_action('admin_post_nh_license_revoke', ['NH_Admin_Actions', 'revoke_license']);

// =====================================================
// AJAX: Mark as Read (single) + Unread Count + CSV Export
// =====================================================

add_action('admin_post_nh_mark_read', function() {
    if (!current_user_can('manage_options')) wp_die('Access denied');
    check_admin_referer('nh_mark_read');

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $id = (int)($_GET['id'] ?? 0);
    if ($id) $wpdb->update($table, ['read_at' => current_time('mysql')], ['id' => $id]);

    wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=nh-dashboard'));
    exit;
});

add_action('wp_ajax_nh_mark_read', function() {
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Forbidden'], 403);
    check_ajax_referer('nh_ajax_nonce', '_wpnonce');

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) wp_send_json_error(['message' => 'Invalid ID'], 400);

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $wpdb->update($table, ['read_at' => current_time('mysql')], ['id' => $id]);

    if ($wpdb->last_error) wp_send_json_error(['message' => $wpdb->last_error], 500);
    wp_send_json_success(['id' => $id]);
});

add_action('wp_ajax_nh_get_unread_count', function() {
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Forbidden'], 403);

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 0 AND read_at IS NULL");

    wp_send_json_success(['count' => $count]);
});

// =====================================================
// Export Notifications to CSV
// =====================================================
add_action('admin_post_nh_export_csv', function() {
    if (!current_user_can('manage_options')) wp_die(__('Access denied.', 'notification-hub'));
    check_admin_referer('nh_export_csv');

    global $wpdb;
    $table = $wpdb->prefix . 'nh_notifications';
    $rows = $wpdb->get_results("SELECT id, source, type, title, message, status, created_at FROM {$table}", ARRAY_A);

    if (empty($rows)) wp_die(__('No notifications found to export.', 'notification-hub'));

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=notification-hub-export.csv');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($rows[0]));
    foreach ($rows as $row) fputcsv($output, $row);
    fclose($output);
    exit;
});
