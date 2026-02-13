<?php
/**
 * Admin Bar Badge Integration
 *
 * Shows unread count in admin bar.
 * (Extracted from NH_Admin_UI)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Bar_Badge implements Integration_Interface {

	public function register(): void {
		add_action( 'admin_bar_menu', array( $this, 'add_badge' ), 100 );
	}

	public function add_badge( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} WHERE status = 0"
		);

		if ( ! $count ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'nh-notifications',
				'title' => sprintf(
					'<span class="ab-icon dashicons-bell"></span><span class="nh-badge">%d</span>',
					$count
				),
				'href'  => admin_url( 'admin.php?page=notification-hub' ),
				'meta'  => array(
					'class' => 'nh-admin-bar-badge',
				),
			)
		);
	}
}
