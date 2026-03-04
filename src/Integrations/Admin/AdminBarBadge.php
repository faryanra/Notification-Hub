<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Admin bar badge (legacy-identical count logic).
 *
 * @since 1.7.2
 */
final class AdminBarBadge implements Integration {
    /**
     * @since 1.7.2
     */
    public function register(Loader $loader): void {
        $loader->addAction('admin_bar_menu', [$this, 'addAdminBarBadge'], 100, 1);
    }

    /**
     * @since 1.7.2
     * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
     */
    public function addAdminBarBadge($wp_admin_bar): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count_new = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE type NOT IN ('dispatch_check','email_sent') AND status IN (0,3) AND read_at IS NULL");

        $title  = '<span class="ab-icon dashicons dashicons-bell"></span>';
        $title .= '<span class="ab-label"> ' . (int) $count_new . ' ' . esc_html__('New', 'notification-hub') . '</span>';

        $wp_admin_bar->add_node([
            'id'    => 'nh_unread',
            'title' => $title,
            'href'  => admin_url('admin.php?page=nh-dashboard'),
            'meta'  => ['title' => esc_html__('View Notifications', 'notification-hub')],
        ]);
    }
}
