<?php
/**
 * Plugin Name: Notification Hub Pro
 * Plugin URI:  https://hellocode.ir/
 * Description: Premium extension for Notification Hub — adds licensing, network policies, analytics and advanced integrations.
 * Version:     1.6.2
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

// Load the Pro layer.
require_once __DIR__ . '/modules/pro/class-nh-pro-loader.php';
new NH_Pro_Loader();

if (!defined('NH_PRO_VERSION')) {
    define('NH_PRO_VERSION', '1.6.2');
}