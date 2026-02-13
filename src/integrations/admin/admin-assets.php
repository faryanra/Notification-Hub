<?php
/**
 * Admin Assets Integration
 *
 * Enqueues admin CSS/JS.
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

class Admin_Assets implements Integration_Interface {

	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue( $hook ) {
		if ( strpos( $hook, 'notification-hub' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'nh-admin',
			NH_PLUGIN_URL . 'assets/css/admin/global.css',
			array(),
			NH_VERSION
		);

		wp_enqueue_script(
			'nh-admin',
			NH_PLUGIN_URL . 'assets/js/admin/global.js',
			array( 'jquery' ),
			NH_VERSION,
			true
		);

		wp_localize_script(
			'nh-admin',
			'nhAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'nh_admin' ),
			)
		);
	}
}
