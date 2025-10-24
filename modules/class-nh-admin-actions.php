<?php
// NH v1.3.2 — Admin Actions (refactored from old Test Controller)
// Same logic, safer execution, no white screens, fully compatible with new loader.

if (!defined('ABSPATH')) exit;

class NH_Admin_Actions {

    public static function init() {
        // register admin_post actions
        add_action('admin_post_nh_test_channel', [__CLASS__, 'handle']);
        add_action('admin_post_nh_test_hook',    [__CLASS__, 'test_hook']);
        add_action('admin_post_nh_save_hook',    [__CLASS__, 'save_hook']);
        add_action('admin_post_nh_update_hook',  [__CLASS__, 'update_hook']);
        add_action('admin_post_nh_delete_hook',  [__CLASS__, 'delete_hook']);
    }

    /* ============================================================
       1️⃣ Channel Test (Email / Telegram / Slack)
    ============================================================ */
    public static function handle() {
        try {
            if (!current_user_can('manage_options')) {
                wp_die(__('Access denied.', 'notification-hub'));
            }

            // Safe nonce check (older forms may not include tab)
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'nh_test_channel')) {
                wp_die(__('Invalid request (nonce).', 'notification-hub'));
            }

            $channel  = sanitize_text_field($_GET['channel'] ?? '');
            $tab      = sanitize_key($_GET['tab'] ?? 'general');

            $registry = NH_Core_Registry::get();
            $notifier = $registry->get_svc('notifier');
            if (!$notifier) {
                wp_die(__('Notifier not found', 'notification-hub'));
            }

            $ok = $notifier->send([
                'channel' => $channel,
                'title'   => '🔔 Notification Hub Test',
                'body'    => 'This is a test message from Notification Hub.',
                'source'  => 'test'
            ]);

            // Redirect back to settings tab (maintains previous tab + notice)
            $redirect = add_query_arg([
                'page'    => 'nh-settings',
                'tab'     => $tab,
                'nh_test' => $channel,
                'success' => $ok ? '1' : '0'
            ], admin_url('admin.php'));

            wp_safe_redirect($redirect);
            exit;

        } catch (Throwable $e) {
            error_log('❌ NH_Admin_Actions::handle(): ' . $e->getMessage());
            wp_die('Test failed: ' . esc_html($e->getMessage()));
        }
    }

    /* ============================================================
       2️⃣ Trigger Test for Custom Hook
    ============================================================ */
    public static function test_hook() {
        try {
            if (!current_user_can('manage_options')) wp_die(__('Access denied.', 'notification-hub'));

            $id = intval($_GET['id'] ?? 0);
            $nonce = $_GET['_wpnonce'] ?? '';

            if (!$id || !wp_verify_nonce($nonce, 'nh_test_' . $id)) {
                wp_die(__('Invalid nonce.', 'notification-hub'));
            }

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));

            if (!$row) wp_die(__('Hook not found.', 'notification-hub'));

            $registry = NH_Core_Registry::get();
            $notifier = $registry->get_svc('notifier');
            if (!$notifier) wp_die(__('Notifier missing.', 'notification-hub'));

            $channels = json_decode($row->channels, true) ?: ['email'];
            $primary  = $channels[0];
            $multi    = array_slice($channels, 1);

            $notifier->send([
                'channel' => $primary,
                'title'   => '🔧 Test for ' . $row->title,
                'body'    => 'Triggered manually via Notification Hub.',
                'source'  => 'hook',
                'multi'   => $multi
            ]);

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_tested=1'));
            exit;
        } catch (Throwable $e) {
            error_log('❌ NH_Admin_Actions::test_hook(): ' . $e->getMessage());
            wp_die('Hook test failed: ' . esc_html($e->getMessage()));
        }
    }

    /* ============================================================
       3️⃣ CRUD: Save / Update / Delete Hooks
    ============================================================ */

    public static function save_hook() {
        try {
            if (!current_user_can('manage_options')) wp_die('Not allowed');
            check_admin_referer('nh_save_hook');

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';

            $title  = sanitize_text_field($_POST['title'] ?? '');
            $action = sanitize_text_field($_POST['action_name'] ?? '');
            $chs    = isset($_POST['channels']) ? array_map('sanitize_text_field', (array)$_POST['channels']) : [];
            $json   = wp_json_encode($chs);

            if ($title && $action) {
                $wpdb->insert($table, [
                    'title'       => $title,
                    'action_name' => $action,
                    'channels'    => $json,
                    'status'      => 1
                ]);
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_saved=1'));
            exit;

        } catch (Throwable $e) {
            error_log('❌ NH_Admin_Actions::save_hook(): ' . $e->getMessage());
            wp_die('Save failed: ' . esc_html($e->getMessage()));
        }
    }

    public static function update_hook() {
        try {
            if (!current_user_can('manage_options')) wp_die('Not allowed');
            $id = intval($_POST['id'] ?? 0);
            check_admin_referer('nh_update_hook_' . $id);

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';

            $title  = sanitize_text_field($_POST['title'] ?? '');
            $action = sanitize_text_field($_POST['action_name'] ?? '');
            $chs    = isset($_POST['channels']) ? array_map('sanitize_text_field', (array)$_POST['channels']) : [];
            $json   = wp_json_encode($chs);

            if ($id > 0) {
                $wpdb->update($table, [
                    'title'       => $title,
                    'action_name' => $action,
                    'channels'    => $json
                ], ['id' => $id], ['%s','%s','%s'], ['%d']);
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_updated=1'));
            exit;
        } catch (Throwable $e) {
            error_log('❌ NH_Admin_Actions::update_hook(): ' . $e->getMessage());
            wp_die('Update failed: ' . esc_html($e->getMessage()));
        }
    }

    public static function delete_hook() {
        try {
            if (!current_user_can('manage_options')) wp_die('Not allowed');
            $id = intval($_GET['id'] ?? 0);
            check_admin_referer('nh_delete_hook_' . $id);

            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'nh_hooks', ['id' => $id], ['%d']);

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_deleted=1'));
            exit;
        } catch (Throwable $e) {
            error_log('❌ NH_Admin_Actions::delete_hook(): ' . $e->getMessage());
            wp_die('Delete failed: ' . esc_html($e->getMessage()));
        }
    }
}

add_action('admin_init', ['NH_Admin_Actions', 'init']);
