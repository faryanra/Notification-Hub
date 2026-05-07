<?php
/**
 * Plugin Name: HelloCode Notification Hub
 * Plugin URI: https://www.hellocode.ir/
 * Description: Centralize WordPress notifications and route them to Email, Telegram, and Slack from one dashboard.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author: Faryan Rajabi (HelloCode)
 * Author URI: https://www.linkedin.com/in/reza-rajabi-jorshari/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: notification-hub
 * Domain Path: /languages
 *
 * @package NotificationHub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NH_PLUGIN_FILE', __FILE__);
define('NH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NH_VERSION', '1.0.0');

define('NH_SRC_DIR', NH_PLUGIN_DIR . 'src/');
define('NH_TEMPLATES_DIR', NH_PLUGIN_DIR . 'templates/');
define('NH_ASSETS_URL', NH_PLUGIN_URL . 'assets/');

/**
 * Load plugin translations.
 *
 * @since 1.0.0
 * @return void
 */
function nh_load_textdomain(): void {
    load_plugin_textdomain('notification-hub', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'nh_load_textdomain', 1);

/**
 * PSR-4 autoloader for NotificationHub\
 *
 * @since 1.0.0
 * @return void
 */
function nh_register_autoloader(): void {
    spl_autoload_register(
        static function (string $class): void {
            $prefix = 'NotificationHub\\';
            $base_dir = NH_SRC_DIR;

            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $relative = str_replace('\\', '/', $relative);

            $candidates = [];

            // 1) Strict PSR-4 path.
            $candidates[] = $base_dir . $relative . '.php';

            // 2) Lowercase namespace directories with original class filename.
            $parts = explode('/', $relative);
            if (count($parts) > 1) {
                $class_file = array_pop($parts);
                $dir_parts = array_map('lcfirst', $parts);
                $candidates[] = $base_dir . implode('/', $dir_parts) . '/' . $class_file . '.php';
            }

            // 3) Fully lowercased fallback for legacy naming.
            $candidates[] = $base_dir . strtolower($relative) . '.php';

            foreach (array_unique($candidates) as $file) {
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    );
}
nh_register_autoloader();

/**
 * Boot plugin.
 *
 * @since 1.0.0
 * @return void
 */
function nh_boot(): void {
    if (!class_exists('NotificationHub\\Main')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('Notification Hub: Missing NotificationHub\\Main.');
        }
        return;
    }

    $main = new \NotificationHub\Main();
    $main->boot();

    // Compatibility hook for optional extensions.
    do_action('nh_loaded');
}
add_action('plugins_loaded', 'nh_boot', 5);
