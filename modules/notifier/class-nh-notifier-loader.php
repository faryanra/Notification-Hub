<?php
/**
 * NH_Notifier_Loader
 *
 * Loads notifier handlers safely.
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier_Loader {

    /**
     * Load handlers.
     *
     * @since 1.7.1
     */
    public static function load(): void {
        self::safe_require_once(__DIR__ . '/class-nh-notifier-queue.php');
        self::safe_require_once(__DIR__ . '/class-nh-notifier-email.php');

        // Premium channels (only when Premium addon is active).
        if (defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE) {
            self::safe_require_once(__DIR__ . '/premium-class-nh-notifier-telegram.php');
            self::safe_require_once(__DIR__ . '/premium-class-nh-notifier-slack.php');
        }
    }

    /**
     * @since 1.7.1
     */
    private static function safe_require_once(string $path): void {
        if (file_exists($path)) {
            require_once $path;
            return;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log(sprintf('Notification Hub: Missing notifier handler file %s', $path));
        }
    }
}