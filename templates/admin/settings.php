<?php
/**
 * Settings Template
 *
 * Entry point for admin settings page.
 * Delegates rendering to the modular settings page template.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$page = NH_PLUGIN_DIR . 'templates/settings/page.php';
if (file_exists($page)) {
    include $page;
    return;
}

wp_die(esc_html__('Settings template not found.', 'notification-hub'));
