<?php
/**
 * Plugin Name: Notification Hub
 * Plugin URI: https://www.hellocode.ir/
 * Description: Central hub for collecting and managing WordPress notifications (Telegram, Email, Slack, WooCommerce, CF7).
 * Version: 2.0.0
 * Author: Faryan Rajabi (HelloCode)
 * Author URI: https://www.linkedin.com/in/reza-rajabi-jorshari/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: notification-hub
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 *
 * @since 2.0.0
 */
define( 'NH_PLUGIN_FILE', __FILE__ );
define( 'NH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NH_VERSION', '2.0.0' );

/**
 * Load plugin textdomain.
 *
 * @since 2.0.0
 * @return void
 */
function nh_load_textdomain(): void {
	load_plugin_textdomain( 'notification-hub', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'nh_load_textdomain', 1 );

/**
 * ============================================================
 * NEW ARCHITECTURE (v2.0.0) - Yoast-style with DI
 * ============================================================
 */
if ( file_exists( NH_PLUGIN_DIR . 'src/bootstrap.php' ) ) {
	require_once NH_PLUGIN_DIR . 'src/bootstrap.php';
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'Notification Hub: Missing bootstrap file (src/bootstrap.php)' );
	}
}

/**
 * Plugin activation callback.
 *
 * @since 2.0.0
 * @return void
 */
function nh_activate(): void {
	// Database migration
	if ( class_exists( 'Notification_Hub\\Initializers\\Database_Migration' ) ) {
		\Notification_Hub\Initializers\Database_Migration::run();
	}

	// Queue migration
	if ( class_exists( 'Notification_Hub\\Initializers\\Queue_Migration' ) ) {
		\Notification_Hub\Initializers\Queue_Migration::run();
	}

	// Capabilities
	if ( class_exists( 'Notification_Hub\\Initializers\\Capabilities' ) ) {
		\Notification_Hub\Initializers\Capabilities::run();
	}

	// Cron schedules
	if ( class_exists( 'Notification_Hub\\Initializers\\Cron_Schedules' ) ) {
		\Notification_Hub\Initializers\Cron_Schedules::run();
	}
}

/**
 * Plugin deactivation callback.
 *
 * @since 2.0.0
 * @return void
 */
function nh_deactivate(): void {
	$timestamp = wp_next_scheduled( 'nh_cron_cleanup' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'nh_cron_cleanup', array() );
	}

	$timestamp = wp_next_scheduled( 'nh_process_queue' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'nh_process_queue', array() );
	}

	wp_clear_scheduled_hook( 'nh_cron_cleanup' );
	wp_clear_scheduled_hook( 'nh_process_queue' );
}

register_activation_hook( NH_PLUGIN_FILE, 'nh_activate' );
register_deactivation_hook( NH_PLUGIN_FILE, 'nh_deactivate' );
