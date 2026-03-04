<?php
/**
 * Plugin Name: Notification Hub
 * Plugin URI: https://www.hellocode.ir/
 * Description: Central hub for collecting and managing WordPress notifications.
 * Version: 1.7.3
 * Author: Faryan Rajabi (HelloCode)
 * Author URI: https://www.linkedin.com/in/reza-rajabi-jorshari/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: notification-hub
 * Domain Path: /languages
 *
 * @package NotificationHub
 * @since 1.7.3
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NH_PLUGIN_FILE', __FILE__);
define('NH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NH_VERSION', '1.7.3');

define('NH_SRC_DIR', NH_PLUGIN_DIR . 'src/');
define('NH_TEMPLATES_DIR', NH_PLUGIN_DIR . 'templates/');
define('NH_ASSETS_URL', NH_PLUGIN_URL . 'assets/');

/**
 * Load plugin translations.
 *
 * @since 1.7.3
 * @return void
 */
function nh_load_textdomain(): void {
    load_plugin_textdomain('notification-hub', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'nh_load_textdomain', 1);

/**
 * PSR-4 autoloader for NotificationHub\
 *
 * @since 1.7.3
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

            $file = $base_dir . $relative . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    );
}
nh_register_autoloader();

if (file_exists(NH_SRC_DIR . 'Compat/legacy-notifier.php')) {
    require_once NH_SRC_DIR . 'Compat/legacy-notifier.php';
}
if (file_exists(NH_SRC_DIR . 'Compat/legacy-admin-actions.php')) {
    require_once NH_SRC_DIR . 'Compat/legacy-admin-actions.php';
}

/**
 * Boot plugin.
 *
 * @since 1.7.3
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

    // Pro addon boot signal (Yoast-like dependency model).
    do_action('nh_loaded');
}
add_action('plugins_loaded', 'nh_boot', 5);
