<?php
/**
 * License capabilities policy.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Capabilities {

    /**
     * Can current user manage license settings.
     *
     * @since 1.7.2
     * @return bool
     */
    public static function can_manage_license(): bool {
        return current_user_can('manage_options');
    }

    /**
     * Can current user view license debug info.
     *
     * @since 1.7.2
     * @return bool
     */
    public static function can_view_license_debug(): bool {
        return current_user_can('manage_options');
    }
}
