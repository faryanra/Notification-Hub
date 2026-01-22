<?php
/**
 * License module bootstrap.
 *
 * Only registers hooks / wires dependencies.
 */

defined('ABSPATH') || exit;

return function ($r, $context = 'admin') {
    // License is Premium-only and is loaded conditionally.
    if (!(defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE)) {
        return;
    }

    // Centralized loading of Premium-only files.
    // Rule: Premium files are identified by filename prefix.
    $premium_files = [
        // License (Premium).
        NH_PLUGIN_DIR . 'modules/premium-class-nh-license.php',

        // Admin actions (Premium).
        NH_PLUGIN_DIR . 'modules/admin-actions/premium-class-nh-admin-license.php',
    ];

    foreach ($premium_files as $file) {
        if (file_exists($file)) {
            require_once $file;
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log(sprintf('Notification Hub: Missing premium file %s', $file));
        }
    }

    // Register license service in registry (legacy class for now).
    if ($r && method_exists($r, 'get_svc') && !$r->get_svc('license') && class_exists('NH_License')) {
        $r->set('license', new NH_License());
    }

    // Premium admin actions.
    if ($context === 'admin') {
        if (class_exists('NH_Admin_License') && method_exists('NH_Admin_License', 'init')) {
            NH_Admin_License::init();
        }
    }
};
