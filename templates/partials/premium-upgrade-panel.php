<?php
/**
 * Premium Upgrade Panel (legacy path)
 *
 * Kept for backward compatibility. Delegates to the new location.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$new = NH_PLUGIN_DIR . 'templates/settings/partials/premium/upgrade-panel.php';
if (file_exists($new)) {
    include $new;
    return;
}

wp_die(esc_html__('Premium upgrade UI not found.', 'notification-hub'));
