<?php
/**
 * Admin Assets
 *
 * Enqueues admin CSS and JS.
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
 * Admin Assets
 */
class Admin_Assets implements Integration_Interface {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( ! $screen || strpos( $screen->id, 'notification-hub' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'notification-hub-admin',
			plugins_url( 'assets/css/admin/global.css', NH_PLUGIN_FILE ),
			array(),
			NH_VERSION
		);

		wp_enqueue_script(
			'notification-hub-admin',
			plugins_url( 'assets/js/admin/global.js', NH_PLUGIN_FILE ),
			array( 'jquery' ),
			NH_VERSION,
			true
		);

		wp_localize_script(
			'notification-hub-admin',
			'nhData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'nh_admin_nonce' ),
			)
		);
	}
}
