<?php
/**
 * Premium License Debug Panel (legacy stub)
 *
 * DEPRECATED: Use templates/settings/partials/premium/license-debug-panel.php instead.
 * Kept for backward compatibility only.
 *
 * @package Notification_Hub
 * @since 1.7.1
 * @deprecated 1.7.2
 */

defined('ABSPATH') || exit;

$new = NH_PLUGIN_DIR . 'templates/settings/partials/premium/license-debug-panel.php';
if (file_exists($new)) {
    include $new;
    return;
}

wp_die(esc_html__('Premium debug panel not found.', 'notification-hub'));
