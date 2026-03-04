<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Security\Capabilities;

/**
 * Admin-post: delete custom hook.
 *
 * Expects: ?action=nh_delete_hook&id=123&_wpnonce=...
 * Nonce: nh_delete_hook_{id}
 *
 * @since 1.7.2
 */
final class DeleteCustomHook {
    public function handle(): void {
        Capabilities::ensureManageOptions();

        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if ($id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_deleted=0'));
            exit;
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'nh_delete_hook_' . $id)) {
            wp_die(esc_html__('Invalid nonce.', 'notification-hub'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $deleted = (bool) $wpdb->delete($table, ['id' => $id], ['%d']);

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&hook_deleted=' . ($deleted ? '1' : '0')));
        exit;
    }
}
