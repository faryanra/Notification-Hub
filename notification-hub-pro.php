<?php
/**
 * Plugin Name: Notification Hub Pro
 * Plugin URI:  https://hellocode.ir/
 * Description: Premium extension for Notification Hub — adds licensing, network policies, analytics and advanced integrations.
 * Version:     1.7.1
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

// Always declare presence/version early.
if (!defined('NH_PRO_ACTIVE')) {
    define('NH_PRO_ACTIVE', true);
}

if (!defined('NH_PRO_VERSION')) {
    define('NH_PRO_VERSION', '1.7.1');
}

// Ensure the core (free) plugin is loaded first.
if (!defined('NH_VERSION')) {
    add_action(
        'admin_notices',
        static function () {
            echo '<div class="notice notice-error"><p>' . esc_html__(
                'Notification Hub Pro requires the free "Notification Hub" plugin to be installed and active.',
                'notification-hub'
            ) . '</p></div>';
        }
    );
    return;
}

// If Free/Pro versions mismatch, do not boot Pro to avoid fatals.
if (defined('NH_VERSION') && (string) NH_VERSION !== (string) NH_PRO_VERSION) {
    add_action(
        'admin_notices',
        static function () {
            $free = defined('NH_VERSION') ? (string) NH_VERSION : 'unknown';
            $pro = defined('NH_PRO_VERSION') ? (string) NH_PRO_VERSION : 'unknown';

            echo '<div class="notice notice-error"><p>' . esc_html(
                sprintf(
                    'Notification Hub Pro version mismatch. Free: %s — Pro: %s. Please install matching versions.',
                    $free,
                    $pro
                )
            ) . '</p></div>';
        }
    );
    return;
}

// Load the Pro layer safely.
$loader = __DIR__ . '/modules/pro/class-nh-pro-loader.php';
if (!file_exists($loader)) {
    add_action(
        'admin_notices',
        static function () {
            echo '<div class="notice notice-error"><p>' . esc_html__(
                'Notification Hub Pro is missing required files (modules/pro/class-nh-pro-loader.php). Please reinstall the Pro addon.',
                'notification-hub'
            ) . '</p></div>';
        }
    );
    return;
}

require_once $loader;

if (!class_exists('NH_Pro_Loader')) {
    add_action(
        'admin_notices',
        static function () {
            echo '<div class="notice notice-error"><p>' . esc_html__(
                'Notification Hub Pro failed to load (NH_Pro_Loader missing). Please reinstall the Pro addon.',
                'notification-hub'
            ) . '</p></div>';
        }
    );
    return;
}

new NH_Pro_Loader();