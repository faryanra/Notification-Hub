<?php
/**
 * Plugin Name: Notification Hub Pro
 * Plugin URI:  https://hellocode.ir/
 * Description: Premium extension for Notification Hub adds licensing, network policies, analytics and advanced integrations.
 * Version:     1.7.2
 * Author:      HelloCode Team
 * Author URI:  https://hellocode.ir/
 * Text Domain: notification-hub
 * Domain Path: /languages
 *
 * @package Notification_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('NH_PRO_ACTIVE')) {
    define('NH_PRO_ACTIVE', true);
}

if (!defined('NH_PRO_VERSION')) {
    define('NH_PRO_VERSION', defined('NH_VERSION') ? (string) NH_VERSION : '1.7.2');
}

/**
 * Admin notice shown when Free is missing.
 */
function nh_pro_notice_requires_free(): void {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    echo '<div class="notice notice-error"><p>' . esc_html__(
        'Notification Hub Pro requires the free "Notification Hub" plugin to be installed and active.',
        'notification-hub'
    ) . '</p></div>';
}

/**
 * Boot Premium integrations from Free codebase after Free finishes loading.
 */
function nh_pro_boot_from_free(): void {
    static $booted = false;
    if ($booted) {
        return;
    }
    $booted = true;

    if (!class_exists('NotificationHub\\Loader') || !class_exists('NotificationHub\\Premium\\Bootstrap')) {
        if (is_admin()) {
            add_action(
                'admin_notices',
                static function (): void {
                    if (!current_user_can('activate_plugins')) {
                        return;
                    }

                    echo '<div class="notice notice-error"><p>' . esc_html__(
                        'Notification Hub Pro could not bootstrap premium classes from Notification Hub Free.',
                        'notification-hub'
                    ) . '</p></div>';
                }
            );
        }
        return;
    }

    $legacy_license = defined('NH_SRC_DIR') ? NH_SRC_DIR . 'Compat/legacy-license.php' : '';
    if ($legacy_license !== '' && file_exists($legacy_license)) {
        require_once $legacy_license;
    }

    $loader = new \NotificationHub\Loader();
    $premium = new \NotificationHub\Premium\Bootstrap();
    $premium->register($loader);
    $loader->run();
}

add_action('nh_loaded', 'nh_pro_boot_from_free', 5);
add_action(
    'plugins_loaded',
    static function (): void {
        if (!defined('NH_VERSION')) {
            add_action('admin_notices', 'nh_pro_notice_requires_free');
            return;
        }

        if (did_action('nh_loaded') > 0) {
            nh_pro_boot_from_free();
        }
    },
    20
);
