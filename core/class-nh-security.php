<?php
// Security helper
// Centralizes capability check, nonce verification, and nonce generation.

if (!defined('ABSPATH')) exit;

class NH_Security {

    /**
     * Check if current user is allowed to manage plugin settings.
     * Dies with message if not.
     */
    public static function ensure_cap() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied.', 'notification-hub'));
        }
    }

    /**
     * Verify nonce for an action, optionally scoped by an ID.
     * Example usage:
     *  NH_Security::verify_nonce('nh_update_hook', $id);
     *
     * This expects the form to have:
     *  wp_nonce_field('nh_update_hook_' . $id);
     */
    public static function verify_nonce($action_base, $id = 0) {

        $nonce = $_REQUEST['_wpnonce'] ?? '';
        $action = $id ? $action_base . '_' . $id : $action_base;

        if (!$nonce || !wp_verify_nonce($nonce, $action)) {
            wp_die(__('Invalid request (nonce). Please refresh and try again.', 'notification-hub'));
        }
    }

    /**
     * Echoes a hidden nonce field in forms.
     * Usage in templates:
     *   NH_Security::nonce_field('nh_save_hook');
     * or:
     *   NH_Security::nonce_field('nh_update_hook', $hook_id);
     */
    public static function nonce_field($action_base, $id = 0) {
        $action = $id ? $action_base . '_' . $id : $action_base;
        wp_nonce_field($action);
    }

    /**
     * Sanitize a list of channels.
     * Guarantees it's an array of clean strings and strips empties.
     */
    public static function sanitize_channels($maybe_channels) {
        if (!is_array($maybe_channels)) {
            return [];
        }
        $clean = [];
        foreach ($maybe_channels as $ch) {
            $s = sanitize_text_field($ch);
            if ($s !== '') {
                $clean[] = $s;
            }
        }
        return $clean;
    }

    /**
     * Validate a custom hook action name.
     * We don't want spaces or weird chars in `action_name` because it can become a WP hook.
     * Allowed: letters, numbers, underscore, dash, dot.
     */
    public static function validate_action_name($raw) {
        $raw = sanitize_text_field($raw);
        // Replace illegal chars with underscore
        $safe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $raw);
        return $safe;
    }

    /**
     * Safe integer getter from request.
     */
    public static function request_int($key) {
        if (isset($_POST[$key])) return intval($_POST[$key]);
        if (isset($_GET[$key]))  return intval($_GET[$key]);
        return 0;
    }
}
