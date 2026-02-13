<?php
/**
 * Admin Bar Badge Integration
 *
 * Adds unread notification count badge to admin bar.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin_Bar_Badge Class
 */
class Admin_Bar_Badge implements Integration_Interface {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_bar_menu', array( $this, 'add_badge' ), 100 );
	}

	/**
	 * Add unread badge to admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_badge( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		// Count active + unread notifications.
		$count_new = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} WHERE status IN (0,3) AND read_at IS NULL"
		);

		$title  = '<span class="ab-icon dashicons dashicons-bell"></span>';
		$title .= '<span class="ab-label"> ' . (int) $count_new . ' ' . esc_html__( 'New', 'notification-hub' ) . '</span>';

		$wp_admin_bar->add_node(
			array(
				'id'    => 'nh_unread',
				'title' => $title,
				'href'  => admin_url( 'admin.php?page=nh-dashboard' ),
				'meta'  => array( 'title' => esc_html__( 'View Notifications', 'notification-hub' ) ),
			)
		);
	}
}
