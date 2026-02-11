<?php
/**
 * Premium Settings Fields (legacy stub)
 *
 * DEPRECATED: Use templates/settings/partials/premium/settings-fields.php instead.
 * Kept for backward compatibility only.
 *
 * @package Notification_Hub
 * @since 1.7.1
 * @deprecated 1.7.2
 */

defined('ABSPATH') || exit;

$new = NH_PLUGIN_DIR . 'templates/settings/partials/premium/settings-fields.php';
if (file_exists($new)) {
    include $new;
    return;
}

wp_die(esc_html__('Premium settings fields not found.', 'notification-hub'));
