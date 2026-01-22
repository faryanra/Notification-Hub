<?php
/**
 * License service.
 *
 * Orchestrates license operations. During step-1 refactor, this service is a thin
 * wrapper around the legacy NH_License facade.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Service {

    /**
     * Read normalized state.
     *
     * @since 1.7.2
     * @return array
     */
    public function read_state(): array {
        return class_exists('NH_License') ? (array) NH_License::get_state() : [];
    }

    /**
     * Refresh state if needed.
     *
     * @since 1.7.2
     * @return void
     */
    public function maybe_refresh(): void {
        if (class_exists('NH_License')) {
            NH_License::maybe_refresh();
        }
    }

    /**
     * Refresh now.
     *
     * @since 1.7.2
     * @return void
     */
    public function refresh_now(): void {
        if (class_exists('NH_License')) {
            // For now, force refresh by resetting last_check.
            $state = (array) NH_License::get_state();
            $state['last_check'] = 0;
            NH_License::set_state($state);
            NH_License::maybe_refresh();
        }
    }

    /**
     * Revoke license.
     *
     * @since 1.7.2
     * @return void
     */
    public function revoke(): void {
        if (class_exists('NH_License')) {
            NH_License::revoke();
        }
    }
}
