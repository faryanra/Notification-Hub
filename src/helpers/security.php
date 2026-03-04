<?php

namespace NotificationHub\Helpers;

/**
 * Security helpers.
 *
 * @since 1.7.2
 */
final class Security {
    /**
     * Ensure current user can manage plugin settings.
     *
     * @since 1.7.2
     */
    public static function ensureCanManageOptions(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'notification-hub'));
        }
    }
}
