<?php
/**
 * Plugin Name: Notification Hub
 * Description: Unified notification system for WordPress.
 * Version: 2.0.0
 * Author: Your Name
 * Text Domain: notification-hub
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'NH_VERSION', '2.0.0' );
define( 'NH_PLUGIN_FILE', __FILE__ );
define( 'NH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load autoloader
require_once NH_PLUGIN_DIR . '../core/autoloader.php';

// Initialize plugin
add_action( 'plugins_loaded', 'nh_init_plugin', 10 );

function nh_init_plugin() {
	load_plugin_textdomain( 'notification-hub', false, dirname( plugin_basename( NH_PLUGIN_FILE ) ) . '/languages' );

	$bootstrap = new \Notification_Hub\Core\Bootstrap();
	$bootstrap->init();
}

// Activation hook
register_activation_hook( NH_PLUGIN_FILE, 'nh_activate_plugin' );

function nh_activate_plugin() {
	\Notification_Hub\Initializers\Database_Migration::run();
	\Notification_Hub\Initializers\Queue_Migration::run();
	\Notification_Hub\Initializers\Capabilities::run();
	\Notification_Hub\Initializers\Cron_Schedules::run();

	flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook( NH_PLUGIN_FILE, 'nh_deactivate_plugin' );

function nh_deactivate_plugin() {
	wp_clear_scheduled_hook( 'nh_cron_cleanup' );
	wp_clear_scheduled_hook( 'nh_process_queue' );

	flush_rewrite_rules();
}
