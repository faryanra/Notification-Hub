<?php
/**
 * NH_Include
 *
 * Lightweight include helper to avoid scattered require_once calls.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_Include {

    /**
     * Require a PHP file relative to plugin root.
     *
     * Example: NH_Include::once('modules/license/admin/actions/save-bundle.php');
     *
     * @since 1.7.2
     * @param string $relative Relative path from plugin root.
     * @return bool True when file exists and was required.
     */
    public static function once($relative) {
        $relative = ltrim((string) $relative, '/');
        if ($relative === '') {
            return false;
        }

        $path = NH_PLUGIN_DIR . $relative;
        if (!file_exists($path)) {
            return false;
        }

        require_once $path;
        return true;
    }
}
