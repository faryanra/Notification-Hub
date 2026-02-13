<?php
/**
 * Admin Bar Badge
 *
 * Shows unread notification count in admin bar.
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
 * Admin Bar Badge
 */
class Admin_Bar_Badge implements Integration_Interface {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_bar_menu', array( $this, 'add_badge' ), 999 );
	}

	/**
	 * Add badge to admin bar.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_badge( $admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;

		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}nh_notifications WHERE status = 'unread'"
		);

		if ( ! $count ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'    => 'notification-hub',
				'title' => sprintf(
					'<span class="ab-icon dashicons dashicons-bell"></span> %s',
					'<span class="nh-count">' . absint( $count ) . '</span>'
				),
				'href'  => admin_url( 'admin.php?page=notification-hub' ),
			)
		);
	}
}
