<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: update custom hook.
 *
 * @since 1.7.2
 */
final class UpdateCustomHook {
    public function handle(): void {
        Capabilities::ensureManageOptions();

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if ($id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_updated=0'));
            exit;
        }

        check_admin_referer('nh_update_hook_' . $id);

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $actionName = isset($_POST['action_name']) ? sanitize_text_field(wp_unslash($_POST['action_name'])) : '';
        $channels = isset($_POST['channels']) ? (array) wp_unslash($_POST['channels']) : [];
        $channels = array_values(array_filter(array_map('sanitize_key', $channels)));

        if ($title === '' || $actionName === '') {
            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_updated=0&edit=' . $id));
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $updated = (bool) $wpdb->update(
            $table,
            [
                'title'       => $title,
                'action_name' => $actionName,
                'channels'    => wp_json_encode($channels),
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_updated=' . ($updated ? '1' : '0') . '&edit=' . $id));
        exit;
    }
}
