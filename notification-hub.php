<?php
/**
 * Plugin Name: Notification Hub
 * Plugin URI: https://www.hellocode.ir/
 * Description: Central hub for collecting and managing WordPress notifications (Telegram, Email, Slack, WooCommerce, CF7).
 * Version: 1.3.1
 * Author: Faryan Rajabi (HelloCode)
 * Author URI: https://www.linkedin.com/in/reza-rajabi-jorshari/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: notification-hub
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * === Constants =========================================================
 */
define('NH_PLUGIN_FILE', __FILE__);
define('NH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NH_VERSION', '1.3.1');

/**
 * === i18n / Textdomain ================================================
 * Load translations from /languages
 */
add_action('plugins_loaded', function () {
    load_plugin_textdomain(
        'notification-hub',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});

/**
 * === Safe Loader Helper ================================================
 * Tiny wrapper so a missing file doesn't white-screen admin.
 * We'll log it instead of fatal.
 */
function nh_require($path) {
    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log('Notification Hub: Missing file ' . $path);
    }
}

/**
 * === Core Includes =====================================================
 * Order matters: registry → helpers/security/db → loader.
 */
nh_require(NH_PLUGIN_DIR . 'core/class-nh-core-registry.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-helpers.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-security.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-database.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-loader.php');
nh_require(NH_PLUGIN_DIR . 'core/class-nh-test-controller.php');

/**
 * Admin / Modules (UI, Dashboard, Actions, etc)
 * NOTE: nh-admin-actions.php is the moved/renamed version of old core/class-nh-test-controller.php
 */
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-admin-ui.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-dashboard.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-custom-hooks.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-notifier.php');
nh_require(NH_PLUGIN_DIR . 'modules/class-nh-license.php');

/**
 * Integrations
 */
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-wp-core.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-woocommerce.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-cf7.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-email.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-telegram.php');
nh_require(NH_PLUGIN_DIR . 'integrations/class-nh-slack.php');

/**
 * API Layer
 */
nh_require(NH_PLUGIN_DIR . 'api/class-nh-restapi.php');
nh_require(NH_PLUGIN_DIR . 'api/class-nh-webhook.php');

/**
 * === Activation / Deactivation ========================================
 * - create/update DB schema
 * - schedule cron cleanup
 */
function nh_activate() {
    // DB setup
    if (class_exists('NH_Database')) {
        $db = new NH_Database();
        $db->maybe_upgrade_database();
    }

    // Schedule daily cleanup if not already
    if (!wp_next_scheduled('nh_cron_cleanup')) {
        wp_schedule_event(time() + 3600, 'daily', 'nh_cron_cleanup');
    }
}

function nh_deactivate() {
    // Unschedule cleanup cron
    $timestamp = wp_next_scheduled('nh_cron_cleanup');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'nh_cron_cleanup');
    }
}

register_activation_hook(NH_PLUGIN_FILE, 'nh_activate');
register_deactivation_hook(NH_PLUGIN_FILE, 'nh_deactivate');

/**
 * === Cron Task =========================================================
 * Cleanup old notifications based on retention setting.
 */
add_action('nh_cron_cleanup', function () {
    if (!class_exists('NH_Database')) return;
    $db = new NH_Database();
    $db->cleanup_old();
});

/**
 * === Boot Sequence =====================================================
 * Build registry, attach core services, hand over to Loader.
 */
function nh_boot() {

    // Registry is our service container
    if (!class_exists('NH_Core_Registry')) {
        error_log('Notification Hub: Registry not available.');
        return;
    }

    $r = NH_Core_Registry::get();

    // Register shared services
    if (class_exists('NH_Database')) {
        $r->set('db', new NH_Database());
    }

    if (class_exists('NH_Security')) {
        $r->set('security', new NH_Security());
    }

    if (class_exists('NH_Helpers')) {
        $r->set('helpers', new NH_Helpers());
    }

    // Loader will wire up admin UI, integrations, REST API, etc.
    if (class_exists('NH_Loader')) {
        $loader = new NH_Loader($r);
        $loader->boot();
    } else {
        error_log('Notification Hub: Loader missing.');
    }
}
add_action('plugins_loaded', 'nh_boot', 5);