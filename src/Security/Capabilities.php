<?php
namespace NotificationHub\Security;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Capabilities helper.
 *
 * @since 1.0.0
 */
final class Capabilities {
    /**
     * Ensure current user can manage plugin settings.
     *
     * @since 1.0.0
     */
    public static function ensureManageOptions(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Forbidden', 'notification-hub'), 403);
        }
    }
}

