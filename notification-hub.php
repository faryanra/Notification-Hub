<?php
/**
 * Plugin Name: Notification Hub
 * Plugin URI: https://www.hellocode.ir/
 * Description: Central hub for collecting and managing WordPress notifications (Telegram, Email, Slack, WooCommerce, CF7).
 * Version: 1.3.0
 * Author: Faryan Rajabi (HelloCode)
 * Author URI: https://www.linkedin.com/in/reza-rajabi-jorshari/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: notification-hub
 * Domain Path: /languages
 */

// NH v1.2.0 — Bootstrap & wiring

if (!defined('ABSPATH')) exit;

define('NH_VERSION', '1.3.0');
define('NH_PLUGIN_FILE', __FILE__);
define('NH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NH_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once NH_PLUGIN_DIR . 'core/class-nh-core-registry.php';
require_once NH_PLUGIN_DIR . 'core/class-nh-helpers.php';
require_once NH_PLUGIN_DIR . 'core/class-nh-security.php';
require_once NH_PLUGIN_DIR . 'core/class-nh-database.php';
require_once NH_PLUGIN_DIR . 'core/class-nh-loader.php';
require_once NH_PLUGIN_DIR . 'modules/class-nh-license.php';
require_once NH_PLUGIN_DIR . 'core/class-nh-test-controller.php';

// i18n loader
add_action('plugins_loaded', function() { 
    // NH v1.2.0 — Load textdomain
    load_plugin_textdomain('notification-hub', false, dirname(plugin_basename(__FILE__)).'/languages');
});

// Activation / DB setup
register_activation_hook(__FILE__, function() {
    // NH v1.2.0 — Ensure DB schema
    $db = new NH_Database();
    $db->maybe_upgrade_database();

    // NH v1.2.0 — Schedule cleanup cron if not exists
    if (!wp_next_scheduled('nh_cron_cleanup')) {
        wp_schedule_event(time() + 60, 'daily', 'nh_cron_cleanup');
    }
});

// Deactivation: Unschedule
register_deactivation_hook(__FILE__, function() {
    // NH v1.2.0 — Unschedule cron
    $timestamp = wp_next_scheduled('nh_cron_cleanup');
    if ($timestamp) wp_unschedule_event($timestamp, 'nh_cron_cleanup');
});

// Cron: cleanup old notifications (uses Retention setting)
add_action('nh_cron_cleanup', function() {
    // NH v1.2.0 — Cleanup old notifications via retention policy
    $days = (int) get_option('nh_retention_days', 90);
    (new NH_Database())->cleanup_old($days);
});

// Boot Loader
add_action('init', function() {
    // NH v1.2.0 — Build Registry & wire modules/integrations
    $registry = NH_Core_Registry::get();
    $registry->set('db', new NH_Database());
    $registry->set('security', new NH_Security());
    $registry->set('helpers', new NH_Helpers());

    $loader = new NH_Loader($registry);
    $loader->boot();
});