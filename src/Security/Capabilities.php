<?php

namespace NotificationHub\Security;

/**
 * Capabilities helper.
 *
 * @since 1.7.2
 */
final class Capabilities {
    /**
     * Ensure current user can manage plugin settings.
     *
     * @since 1.7.2
     */
    public static function ensureManageOptions(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }
    }
}
