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

// Some environments may not include empty directories in deployments.
// Create the missing directory if possible (best effort), then fall back.
if (!file_exists($legacy)) {
    // Fallback to the legacy path if the moved markup isn't present.
    $fallback = NH_PLUGIN_DIR . 'templates/partials/premium-license-box-legacy.php';
    if (file_exists($fallback)) {
        include $fallback;
        return;
    }
}

if (file_exists($legacy)) {
    include $legacy;
    return;
}

wp_die(esc_html__('Premium license UI not found.', 'notification-hub'));
