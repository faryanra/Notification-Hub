<?php
/**
 * Premium License Box (new path)
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

// Include original template (moved from templates/partials/).
// This file is intentionally kept as a thin wrapper so the markup stays unchanged.

$legacy = NH_PLUGIN_DIR . 'templates/partials/__moved/premium-license-box.php';
if (file_exists($legacy)) {
    include $legacy;
    return;
}

wp_die(esc_html__('Premium license UI not found.', 'notification-hub'));
