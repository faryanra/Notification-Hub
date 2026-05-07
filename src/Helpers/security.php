<?php
namespace NotificationHub\Helpers;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security helpers.
 *
 * @since 1.0.0
 */
final class Security {
    /**
     * Ensure current user can manage plugin settings.
     *
     * @since 1.0.0
     */
    public static function ensureCanManageOptions(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'notification-hub'));
        }
    }
}

