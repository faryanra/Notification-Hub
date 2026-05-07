<?php
namespace NotificationHub\Security;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Services\EventLogger;
use WP_Error;
use WP_REST_Request;

/**
 * REST permission helpers.
 *
 * Enforces both capability and WP REST nonce checks for admin-only routes.
 *
 * @since 1.0.0
 */
final class RestGuard {
    /**
     * Require manage_options + valid wp_rest nonce.
     *
     * @return true|WP_Error
     */
    public static function requireAdminAndNonce(WP_REST_Request $request) {
        if (!current_user_can('manage_options')) {
            EventLogger::warn('rest', 'rest_forbidden', 'REST access denied by capability', []);
            return new WP_Error(
                'nh_rest_forbidden',
                __('You are not allowed to access this endpoint.', 'notification-hub'),
                ['status' => 403]
            );
        }

        $nonce = (string) $request->get_header('x-wp-nonce');
        if ($nonce === '' || !wp_verify_nonce($nonce, 'wp_rest')) {
            EventLogger::warn('rest', 'rest_invalid_nonce', 'REST nonce check failed', []);
            return new WP_Error(
                'nh_rest_invalid_nonce',
                __('Invalid REST nonce.', 'notification-hub'),
                ['status' => 401]
            );
        }

        return true;
    }
}

