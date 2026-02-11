<?php
/**
 * Premium Upgrade Panel (legacy stub)
 *
 * DEPRECATED: Use templates/settings/partials/premium/upgrade-panel.php instead.
 * Kept for backward compatibility only.
 *
 * @package Notification_Hub
 * @since 1.7.1
 * @deprecated 1.7.2
 */

defined('ABSPATH') || exit;

$new = NH_PLUGIN_DIR . 'templates/settings/partials/premium/upgrade-panel.php';
if (file_exists($new)) {
    include $new;
    return;
}

wp_die(esc_html__('Premium upgrade panel not found.', 'notification-hub'));
