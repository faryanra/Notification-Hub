<?php

/**
 * Legacy admin-actions loader (compat).
 *
 * New architecture registers all routes in src/Integrations/Admin/*.
 * This file remains for old code that still requires modules/class-nh-admin-actions.php.
 *
 * @since 1.7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NH_Admin_Actions')) {
    class NH_Admin_Actions {
        public static function load_modules(): void {
            // No-op.
        }

        public static function init(): void {
            // No-op.
        }
    }
}
