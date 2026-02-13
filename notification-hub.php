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
 * Requires at least: 5.9
 * Requires PHP: 7.4
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
require_once NH_PLUGIN_DIR . 'src/core/autoloader.php';

/**
 * Initialize plugin
 *
 * @since 2.0.0
 * @return void
 */
function nh_init_plugin() {
	load_plugin_textdomain( 'notification-hub', false, dirname( plugin_basename( NH_PLUGIN_FILE ) ) . '/languages' );

	$bootstrap = new \Notification_Hub\Core\Bootstrap();
	$bootstrap->init();
}
add_action( 'plugins_loaded', 'nh_init_plugin', 10 );

/**
 * Activation hook
 *
 * @since 2.0.0
 * @return void
 */
function nh_activate_plugin() {
	require_once NH_PLUGIN_DIR . 'src/core/autoloader.php';

	\Notification_Hub\Initializers\Database_Migration::run();
	\Notification_Hub\Initializers\Queue_Migration::run();
	\Notification_Hub\Initializers\Capabilities::run();
	\Notification_Hub\Initializers\Cron_Schedules::run();

	flush_rewrite_rules();
}
register_activation_hook( NH_PLUGIN_FILE, 'nh_activate_plugin' );

/**
 * Deactivation hook
 *
 * @since 2.0.0
 * @return void
 */
function nh_deactivate_plugin() {
	wp_clear_scheduled_hook( 'nh_cron_cleanup' );
	wp_clear_scheduled_hook( 'nh_process_queue' );

	flush_rewrite_rules();
}
register_deactivation_hook( NH_PLUGIN_FILE, 'nh_deactivate_plugin' );
