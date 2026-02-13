<?php
/**
 * Menu Registration Integration
 *
 * Registers admin menu pages.
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

class Menu_Registration implements Integration_Interface {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	public function add_menu() {
		add_menu_page(
			__( 'Notification Hub', 'notification-hub' ),
			__( 'Notifications', 'notification-hub' ),
			'manage_options',
			'notification-hub',
			array( $this, 'render_dashboard' ),
			'dashicons-bell',
			25
		);

		add_submenu_page(
			'notification-hub',
			__( 'Dashboard', 'notification-hub' ),
			__( 'Dashboard', 'notification-hub' ),
			'manage_options',
			'notification-hub',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'notification-hub',
			__( 'Settings', 'notification-hub' ),
			__( 'Settings', 'notification-hub' ),
			'manage_options',
			'notification-hub-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'notification-hub',
			__( 'Custom Hooks', 'notification-hub' ),
			__( 'Custom Hooks', 'notification-hub' ),
			'manage_options',
			'notification-hub-hooks',
			array( $this, 'render_hooks' )
		);
	}

	public function render_dashboard() {
		// TODO: Load dashboard presenter
		echo '<h1>Dashboard</h1>';
	}

	public function render_settings() {
		// TODO: Load settings presenter
		echo '<h1>Settings</h1>';
	}

	public function render_hooks() {
		// TODO: Load hooks presenter
		echo '<h1>Custom Hooks</h1>';
	}
}
