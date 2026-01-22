<?php
/**
 * Premium Settings Fields (new path)
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$legacy = NH_PLUGIN_DIR . 'templates/partials/__moved/premium-settings-fields.php';
if (file_exists($legacy)) {
    include $legacy;
    return;
}

wp_die(esc_html__('Premium settings fields UI not found.', 'notification-hub'));
