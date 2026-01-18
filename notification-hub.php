<?php
/**
 * Plugin Name: Notification Hub
 * Plugin URI: https://www.hellocode.ir/
 * Description: Central hub for collecting and managing WordPress notifications (Telegram, Email, Slack, WooCommerce, CF7).
 * Version: 1.6.3
 * Author: Faryan Rajabi (HelloCode)
 * Author URI: https://www.linkedin.com/in/reza-rajabi-jorshari/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: notification-hub
 * Domain Path: /languages
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin constants.
 *
 * @since 1.6.2
 */
define('NH_PLUGIN_FILE', __FILE__);
define('NH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NH_VERSION', '1.6.3');

/**
 * Load plugin textdomain.
 *
 * @since 1.6.2
 * @return void
 */
function nh_load_textdomain(): void {
    load_plugin_textdomain('notification-hub', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'nh_load_textdomain', 1);

/**
 * Safe require helper.
 *
 * @since 1.6.2
 *
 * @param string $path Absolute file path.
 * @return bool True when loaded, false otherwise.
 */
function nh_require(string $path): bool {
    if (file_exists($path)) {
        require_once $path;
        return true;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log(sprintf('Notification Hub: Missing file %s', $path));
    }

    return false;
}

/**
 * Core includes (order matters).
 *
 * @since 1.6.2
 */
nh_require(NH_PLUGIN_DIR . 'core/class-nh-core-registry.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-helpers.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-human.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-security.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-database.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-queue.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-loader.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-template.php');

/**
 * Modules / Admin.
 *
 * @since 1.6.2
 */
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-license.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-admin-ui.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-dashboard.php');
nh_require(NH_PLUGIN_DIR . 'modules/dashboard/class-nh-notifications-table.php');
nh_require(NH_PLUGIN_DIR . 'modules/dashboard/class-nh-dashboard-actions.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-custom-hooks.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-notifier.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-admin-actions.php');

/**
 * Integrations.
 *
 * @since 1.6.2
 */
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-wp-core.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-woocommerce.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-cf7.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-email.php');

/**
 * API layer.
 *
 * @since 1.6.2
 */
nh_require(NH_PLUGIN_DIR . 'api/class-nh-restapi.php');
nh_require(NH_PLUGIN_DIR . 'api/class-nh-webhook.php');

/**
 * Anti-tamper (light).
 *
 * @since 1.6.2
 * @return void
 */
function nh_security_boot(): void {
    if (class_exists('NH_Security')) {
        NH_Security::anti_tamper_light();
    }
}
add_action('plugins_loaded', 'nh_security_boot', 2);

/**
 * Plugin activation callback.
 *
 * @since 1.6.2
 * @return void
 */
function nh_activate(): void {
    if (class_exists('NH_Database')) {
        (new NH_Database())->maybe_upgrade_database();
    }

    if (!wp_next_scheduled('nh_cron_cleanup')) {
        wp_schedule_event(time() + 3600, 'daily', 'nh_cron_cleanup');
    }
}

/**
 * Plugin deactivation callback.
 *
 * @since 1.6.2
 * @return void
 */
function nh_deactivate(): void {
    $timestamp = wp_next_scheduled('nh_cron_cleanup');
    if ($timestamp) {
        wp_unschedule_event($timestamp);
    }
}

register_activation_hook(NH_PLUGIN_FILE, 'nh_activate');
register_deactivation_hook(NH_PLUGIN_FILE, 'nh_deactivate');

/**
 * Cron cleanup task.
 *
 * @since 1.6.2
 * @return void
 */
function nh_cron_cleanup_handler(): void {
    if (!class_exists('NH_Database')) {
        return;
    }

    (new NH_Database())->cleanup_old();
}
add_action('nh_cron_cleanup', 'nh_cron_cleanup_handler');

/**
 * Boot plugin services.
 *
 * @since 1.6.2
 * @return void
 */
function nh_boot(): void {
    if (!class_exists('NH_Core_Registry') || !class_exists('NH_Loader')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('Notification Hub: Boot failed (Registry/Loader missing).');
        }
        return;
    }

    $r = NH_Core_Registry::get();

    if (class_exists('NH_Database')) {
        $r->set('db', new NH_Database());
    }

    if (class_exists('NH_Security')) {
        $r->set('security', new NH_Security());
    }

    if (class_exists('NH_Helpers')) {
        $r->set('helpers', new NH_Helpers());
    }

    if (class_exists('NH_Notifier')) {
        $r->set('notifier', new NH_Notifier($r));
    }

    (new NH_Loader($r))->boot();
}
add_action('plugins_loaded', 'nh_boot', 5);