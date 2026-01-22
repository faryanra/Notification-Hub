<?php
/**
 * Integrations module bootstrap.
 *
 * Wires integrations that listen to WordPress events and push notifications into DB/queue.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

return function ($r, $context = 'frontend') {
    if (!$r) {
        return;
    }

    $integrations = [
        'NH_Int_WP_Core',
        'NH_Int_WooCommerce',
        'NH_Int_CF7',
        'NH_Email',
    ];

    foreach ($integrations as $cls) {
        if (!class_exists($cls)) {
            continue;
        }

        try {
            // Keep legacy behavior: prefer init($registry) when available.
            if (method_exists($cls, 'init')) {
                call_user_func([$cls, 'init'], $r);
                continue;
            }

            new $cls($r);
        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log(sprintf('Notification Hub: Integration %s failed: %s', $cls, $e->getMessage()));
            }
        }
    }
};
