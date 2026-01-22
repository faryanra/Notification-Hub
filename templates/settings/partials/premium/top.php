<?php
/**
 * Premium Top Panels
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$partials_root = NH_PLUGIN_DIR . 'templates/partials/';

$license_partial = $partials_root . 'premium-license-box.php';
if (file_exists($license_partial)) {
    include $license_partial;
}

if (defined('WP_DEBUG') && WP_DEBUG) {
    $debug_partial = $partials_root . 'premium-license-debug-panel.php';
    if (file_exists($debug_partial)) {
        include $debug_partial;
    }
}
