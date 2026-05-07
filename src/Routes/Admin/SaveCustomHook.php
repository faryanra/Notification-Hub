<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: create custom hook.
 *
 * @since 1.0.0
 */
final class SaveCustomHook {
    public function handle(): void {
        Capabilities::ensureManageOptions();
        check_admin_referer('nh_save_hook');

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $actionName = isset($_POST['action_name']) ? sanitize_text_field(wp_unslash($_POST['action_name'])) : '';
        $channels = isset($_POST['channels']) ? (array) wp_unslash($_POST['channels']) : [];
        $channels = array_values(array_filter(array_map('sanitize_key', $channels)));

        if ($title === '' || $actionName === '') {
            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_saved=0'));
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        $inserted = (bool) $wpdb->insert(
            $table,
            [
                'title'       => $title,
                'action_name' => $actionName,
                'channels'    => wp_json_encode($channels),
                'status'      => 1,
            ],
            ['%s', '%s', '%s', '%d']
        );

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_saved=' . ($inserted ? '1' : '0')));
        exit;
    }
}

