<?php

/**
 * Plugin Name: Notification Hub
 * Plugin URI: #CodeCanyon 
 * Description: A central hub for aggregating notifications from WordPress, WooCommerce, forms, and external services like Telegram. Focuses on simple UX for easy management.
 * Version: 1.0.0
 * Author: Faryan Rajabi
 * Author URI: https://hellocode.ir/ _ https://www.linkedin.com/in/faryan-rajabi/
 * Github : https://github.com/faryanra/
 * Text Domain: notification-hub
 * Domain Path: /languages
 * Requires at least: 6.0  // Minimum WordPress version to ensure compatibility
 * Requires PHP: 8.0     // Minimum PHP version for modern features
 * License: GPL-2.0-or-later
 */

// Prevent direct access to this file for security reasons
if (! defined('ABSPATH')) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

// Define constants for paths and versions to make code reusable and avoid hardcoding
define('NH_VERSION', '1.0.0');  // Plugin version for easy updates and checks
define('NH_PATH', plugin_dir_path(__FILE__));  // Full path to plugin directory
define('NH_URL', plugin_dir_url(__FILE__));   // URL to plugin directory for assets like CSS/JS

// Load required classes and files – this keeps the main file clean
require_once NH_PATH . 'includes/class-nh-loader.php';  // Loads the main loader class to handle actions/filters
require_once NH_PATH . 'includes/class-nh-database.php';  // Loads the database class for table creation (must be before activation hook)
require_once NH_PATH . 'includes/class-nh-collector.php';  // Loads the collector class for gathering and storing notifications
require_once NH_PATH . 'includes/class-nh-notifier.php';  // Loads notifier for sending
if ( file_exists( NH_PATH . 'pro/class-nh-pro-features.php' ) ) {
    require_once NH_PATH . 'pro/class-nh-pro-features.php';  // Loads pro features if folder exists
}

// Hook to create database table on plugin activation
register_activation_hook( __FILE__, [ 'NH_Database', 'create_table' ] );  // Calls create_table method only when plugin is activated

// Instantiate the loader class to initialize the plugin
$nh_loader = new NH_Loader();  // Creates an instance to run constructor hooks