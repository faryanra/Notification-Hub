<?php
/**
 * Hooks Page Presenter
 *
 * Renders the Hooks management page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks_Page Class
 */
class Hooks_Page {

	/**
	 * Render hooks page.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'notification-hub' ) );
		}

		$file = defined( 'NH_PLUGIN_DIR' ) ? NH_PLUGIN_DIR . 'templates/hooks.php' : '';

		if ( $file && file_exists( $file ) ) {
			include $file;
			return;
		}

		echo '<div class="wrap"><h1>' . esc_html__( 'Hooks', 'notification-hub' ) . '</h1><p>' . esc_html__( 'Template not found.', 'notification-hub' ) . '</p></div>';
	}
}
