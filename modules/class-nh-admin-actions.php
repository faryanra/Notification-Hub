<?php
/**
 * NH_Admin_Actions
 *
 * Admin actions coordinator. Loads admin action sub-modules (license, hooks, CSV export)
 * and boots them on admin_init.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Admin_Actions {

    /**
     * Load admin-actions submodules.
     *
     * Load order:
     * - Prefer premium-prefixed file when present (so Premium ZIP can ship just premium-* files).
     * - Fallback to free file.
     *
     * @since 1.6.2
     * @return void
     */
    public static function load_modules(): void {
        // License handler exists in two forms (free + premium-prefixed). Ensure only one is loaded.
        if (!self::safe_require_once(__DIR__ . '/admin-actions/premium-class-nh-admin-license.php')) {
            self::safe_require_once(__DIR__ . '/admin-actions/class-nh-admin-license.php');
        }

        self::safe_require_once(__DIR__ . '/admin-actions/class-nh-admin-hooks.php');
        self::safe_require_once(__DIR__ . '/admin-actions/class-nh-admin-csv-export.php');
    }

    /**
     * Safe require helper for admin-actions submodules.
     *
     * @since 1.6.2
     * @param string $path Absolute file path.
     * @return bool True when loaded, false otherwise.
     */
    private static function safe_require_once(string $path): bool {
        if (file_exists($path)) {
            require_once $path;
            return true;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log(sprintf('Notification Hub: Missing admin-actions file %s', $path));
        }

        return false;
    }

    /**
     * Initialize all admin action handlers.
     *
     * @since 1.6.2
     * @return void
     */
    public static function init(): void {
        self::load_modules();

        if (class_exists('NH_Admin_License') && method_exists('NH_Admin_License', 'init')) {
            NH_Admin_License::init();
        }

        if (class_exists('NH_Admin_Hooks') && method_exists('NH_Admin_Hooks', 'init')) {
            NH_Admin_Hooks::init();
        }

        if (class_exists('NH_Admin_CSV_Export') && method_exists('NH_Admin_CSV_Export', 'init')) {
            NH_Admin_CSV_Export::init();
        }
    }
}

// Boot.
add_action('admin_init', ['NH_Admin_Actions', 'init']);
