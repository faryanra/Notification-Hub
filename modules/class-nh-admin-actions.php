<?php
// NH v1.3.3 — Admin Actions / Test Send / Hook CRUD
// Same UX as 1.3.2 (no regression), but now using NH_Security for cap + nonce + sanitization.

if (!defined('ABSPATH')) exit;

class NH_Admin_Actions {

    public static function init() {
        add_action('admin_post_nh_test_channel', [__CLASS__, 'handle_test_channel']);
        add_action('admin_post_nh_test_hook',    [__CLASS__, 'handle_test_hook']);

        add_action('admin_post_nh_save_hook',    [__CLASS__, 'handle_save_hook']);
        add_action('admin_post_nh_update_hook',  [__CLASS__, 'handle_update_hook']);
        add_action('admin_post_nh_delete_hook',  [__CLASS__, 'handle_delete_hook']);
    }

    /**
     * Build redirect URL back to admin page, optionally adding status flags.
     */
    private static function redirect_with($base_args) {
        $ref = wp_get_referer();
        if (!$ref) {
            $ref = admin_url('admin.php');
        }
        $url = add_query_arg($base_args, $ref);
        wp_safe_redirect($url);
        exit;
    }

    /**
     * GET helper: current tab from request for settings redirect.
     */
    private static function current_tab() {
        return isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    }

    /**
     * 1) Send test alert (Email / Telegram / Slack)
     */
    public static function handle_test_channel() {
        try {
            NH_Security::ensure_cap();
            // was: check_admin_referer('nh_test_channel');
            NH_Security::verify_nonce('nh_test_channel');

            $channel  = sanitize_text_field($_GET['channel'] ?? '');
            $tab      = self::current_tab();

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

            // redirect back to same settings screen, same tab, with success flag
            $redirect_args = [
                'page'    => 'nh-settings',
                'tab'     => $tab,
                'nh_test' => $channel,
                'success' => $ok ? '1' : '0'
            ];

            $url = add_query_arg($redirect_args, admin_url('admin.php'));
            wp_safe_redirect($url);
            exit;

        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('❌ NH_Admin_Actions::handle_test_channel: ' . $e->getMessage());
            }
            wp_die('Test failed: ' . esc_html($e->getMessage()));
        }
    }

    /**
     * 2) Manually fire a specific hook ("Test Hook")
     */
    public static function handle_test_hook() {
        try {
            NH_Security::ensure_cap();

            $id = NH_Security::request_int('id');
            $nonce_ok = isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'nh_test_' . $id);
            if (!$id || !$nonce_ok) {
                wp_die(__('Invalid nonce.', 'notification-hub'));
            }

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));
            if (!$row) {
                wp_die(__('Hook not found.', 'notification-hub'));
            }

            $registry = NH_Core_Registry::get();
            $notifier = $registry->get_svc('notifier');
            if (!$notifier) {
                wp_die(__('Notifier missing.', 'notification-hub'));
            }

            $channels = json_decode($row->channels, true);
            if (!is_array($channels) || empty($channels)) {
                $channels = ['email'];
            }

            $primary = $channels[0];
            $multi   = array_slice($channels, 1);

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
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('❌ NH_Admin_Actions::handle_test_hook: ' . $e->getMessage());
            }
            wp_die('Hook test failed: ' . esc_html($e->getMessage()));
        }
    }

    /**
     * 3) Create new hook
     */
    public static function handle_save_hook() {
        try {
            NH_Security::ensure_cap();
            NH_Security::verify_nonce('nh_save_hook');

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';

            $title_raw   = $_POST['title']        ?? '';
            $action_raw  = $_POST['action_name']  ?? '';
            $channels_in = $_POST['channels']     ?? [];

            $title   = sanitize_text_field($title_raw);
            $action  = NH_Security::validate_action_name($action_raw);
            $chs     = NH_Security::sanitize_channels($channels_in);
            $json    = wp_json_encode($chs);

            if ($title && $action) {
                $wpdb->insert($table, [
                    'title'       => $title,
                    'action_name' => $action,
                    'channels'    => $json,
                    'status'      => 1
                ], [
                    '%s','%s','%s','%d'
                ]);

                if (!empty($wpdb->last_error) && defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('❌ NH_Admin_Actions::handle_save_hook DB: ' . $wpdb->last_error);
                }
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_saved=1'));
            exit;

        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('❌ NH_Admin_Actions::handle_save_hook: ' . $e->getMessage());
            }
            wp_die('Save failed: ' . esc_html($e->getMessage()));
        }
    }

    /**
     * 4) Update existing hook
     */
    public static function handle_update_hook() {
        try {
            NH_Security::ensure_cap();

            $id = NH_Security::request_int('id');
            NH_Security::verify_nonce('nh_update_hook', $id);

            global $wpdb;
            $table = $wpdb->prefix . 'nh_hooks';

            $title_raw   = $_POST['title']        ?? '';
            $action_raw  = $_POST['action_name']  ?? '';
            $channels_in = $_POST['channels']     ?? [];

            $title  = sanitize_text_field($title_raw);
            $action = NH_Security::validate_action_name($action_raw);
            $chs    = NH_Security::sanitize_channels($channels_in);
            $json   = wp_json_encode($chs);

            if ($id > 0) {
                $wpdb->update(
                    $table,
                    [
                        'title'       => $title,
                        'action_name' => $action,
                        'channels'    => $json
                    ],
                    ['id' => $id],
                    ['%s','%s','%s'],
                    ['%d']
                );

                if (!empty($wpdb->last_error) && defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('❌ NH_Admin_Actions::handle_update_hook DB: ' . $wpdb->last_error);
                }
            }

            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_updated=1'));
            exit;

        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('❌ NH_Admin_Actions::handle_update_hook: ' . $e->getMessage());
            }
            wp_die('Update failed: ' . esc_html($e->getMessage()));
        }
    }

    /**
     * 5) Delete hook
     */
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
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('❌ NH_Admin_Actions::handle_delete_hook: ' . $e->getMessage());
            }
            wp_die('Delete failed: ' . esc_html($e->getMessage()));
        }
    }
}

// hook into admin lifecycle
add_action('admin_init', ['NH_Admin_Actions', 'init']);
