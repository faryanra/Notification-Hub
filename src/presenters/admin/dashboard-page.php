<?php
/**
 * Dashboard Page Presenter
 *
 * Renders the main Dashboard page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard_Page Class
 */
class Dashboard_Page {

	/**
	 * Render dashboard page.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'notification-hub' ) );
		}

		// TODO: Implement full dashboard rendering.
		// For now, delegate to old NH_Dashboard if exists.
		if ( class_exists( 'NH_Dashboard' ) ) {
			$dashboard = new \NH_Dashboard();
			$dashboard->render();
			return;
		}

		echo '<div class="wrap"><h1>' . esc_html__( 'Dashboard', 'notification-hub' ) . '</h1><p>Coming soon...</p></div>';
	}
}
