<?php
/**
 * Premium License Debug Panel (new path)
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$legacy = NH_PLUGIN_DIR . 'templates/partials/__moved/premium-license-debug-panel.php';
if (file_exists($legacy)) {
    include $legacy;
    return;
}

wp_die(esc_html__('Premium debug UI not found.', 'notification-hub'));
