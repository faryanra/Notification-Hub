<?php
/**
 * Admin Assets Integration
 *
 * Enqueues admin CSS and JavaScript files.
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
 * Admin_Assets Class
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
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		// Base admin JS (global for NH pages).
		wp_enqueue_script(
			'nh-admin',
			defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/js/admin.js' : '',
			array( 'jquery' ),
			defined( 'NH_VERSION' ) ? NH_VERSION : '2.0.0',
			true
		);

		// Localize for AJAX.
		wp_localize_script(
			'nh-admin',
			'nhAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'nh_ajax_nonce' ),
				'i18n'     => array(
					'badge_new' => esc_html__( 'New', 'notification-hub' ),
				),
			)
		);

		// Optional CSS variables.
		$vars_path = defined( 'NH_PLUGIN_DIR' ) ? NH_PLUGIN_DIR . 'assets/css/nh-variables.css' : '';
		if ( $vars_path && file_exists( $vars_path ) ) {
			wp_enqueue_style(
				'nh-variables',
				defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/css/nh-variables.css' : '',
				array(),
				defined( 'NH_VERSION' ) ? NH_VERSION : '2.0.0'
			);
		}

		// Detect current NH admin page.
		$hook        = (string) $hook;
		$is_dashboard = ( 'toplevel_page_nh-dashboard' === $hook ) || ( false !== strpos( $hook, 'nh-dashboard' ) );
		$is_hooks     = ( false !== strpos( $hook, 'nh-hooks' ) );
		$is_settings  = ( false !== strpos( $hook, 'nh_settings' ) );

		if ( ! $is_dashboard && ! $is_hooks && ! $is_settings ) {
			return;
		}

		// Shared admin styles.
		$style_deps = wp_style_is( 'nh-variables', 'enqueued' ) ? array( 'nh-variables' ) : array();
		wp_enqueue_style(
			'nh-admin',
			defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/css/admin.css' : '',
			$style_deps,
			defined( 'NH_VERSION' ) ? NH_VERSION : '2.0.0'
		);

		// Dashboard assets.
		if ( $is_dashboard ) {
			$notif_deps = wp_style_is( 'nh-variables', 'enqueued' ) ? array( 'nh-variables' ) : array();
			wp_enqueue_style(
				'nh-notifications',
				defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/css/notifications.css' : '',
				$notif_deps,
				defined( 'NH_VERSION' ) ? NH_VERSION : '2.0.0'
			);

			wp_enqueue_script(
				'nh-dashboard',
				defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/js/dashboard.js' : '',
				array( 'jquery' ),
				defined( 'NH_VERSION' ) ? NH_VERSION : '2.0.0',
				true
			);

			// JS translations.
			wp_localize_script(
				'nh-dashboard',
				'nh_i18n',
				array(
					'no_ajax'      => esc_html__( 'AJAX URL not available.', 'notification-hub' ),
					'load_error'   => esc_html__( 'Failed to load notification.', 'notification-hub' ),
					'request_fail' => esc_html__( 'Request failed.', 'notification-hub' ),
				)
			);

			// REST config.
			$rest_root = trailingslashit( get_rest_url( null, 'nh/v1' ) );
			wp_localize_script(
				'nh-dashboard',
				'nhREST',
				array(
					'root'       => esc_url_raw( $rest_root ),
					'nonce'      => wp_create_nonce( 'wp_rest' ),
					'server_now' => current_time( 'mysql' ),
				)
			);
		}

		// Settings assets.
		if ( $is_settings ) {
			wp_enqueue_style(
				'nh-settings',
				defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/css/settings.css' : '',
				$style_deps,
				defined( 'NH_VERSION' ) ? NH_VERSION . '-settings' : '2.0.0-settings',
				'all'
			);

			wp_enqueue_script(
				'nh-settings',
				defined( 'NH_PLUGIN_URL' ) ? NH_PLUGIN_URL . 'assets/js/settings.js' : '',
				array(),
				defined( 'NH_VERSION' ) ? NH_VERSION . '-settings-v2' : '2.0.0-settings-v2',
				true
			);
		}
	}
}
