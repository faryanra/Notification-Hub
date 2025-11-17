<?php
// Security helper
// Centralizes capability check, nonce verification, sanitization utilities.

if (!defined('ABSPATH')) exit;

class NH_Security {

    /**
     * ensure_cap()
     * Abort if current user cannot manage plugin settings.
     * All admin_post handlers should call this first.
     */
    public static function ensure_cap() {
        if (!current_user_can('manage_options')) {
            wp_die(
                esc_html__('Access denied.', 'notification-hub')
            );
        }
    }

    /**
     * verify_nonce()
     * Validate request nonce.
     *
     * $action_base: string like 'nh_save_hook'
     * $id: optional numeric context, e.g. hook ID
     *
     * Form must have:
     *   NH_Security::nonce_field('nh_save_hook');
     * or:
     *   NH_Security::nonce_field('nh_update_hook', $id);
     */
    public static function verify_nonce($action_base, $id = 0) {
        $nonce  = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        $action = $id ? $action_base . '_' . $id : $action_base;

        if (!$nonce || !wp_verify_nonce($nonce, $action)) {

            // only log in debug mode so production users
            if (defined('WP_DEBUG') && WP_DEBUG && strpos(home_url(), 'localhost') !== false) {
                error_log("⚡ NH_Queue: immediate execution (dev localhost) for {$channel}");
                do_action('nh_process_send', $channel, $payload);
                return;
            }

            wp_die(
                esc_html__('Invalid request (nonce). Please refresh and try again.', 'notification-hub')
            );
        }
    }

    /**
     * nonce_field()
     * Output nonce field for use in forms.
     */
    public static function nonce_field($action_base, $id = 0) {
        $action = $id ? $action_base . '_' . $id : $action_base;
        wp_nonce_field($action);
    }

    /**
     * sanitize_channels()
     * Clean & normalize list of channels selected (email/telegram/slack).
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
     * validate_action_name()
     * Make sure a custom hook action_name is safe to be used in do_action().
     */
    public static function validate_action_name($raw) {
        $raw  = sanitize_text_field($raw);
        $safe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $raw);
        return $safe;
    }

    /**
     * request_int()
     * Safely pull an integer from either POST or GET.
     */
    public static function request_int($key) {
        if (isset($_POST[$key])) return intval($_POST[$key]);
        if (isset($_GET[$key]))  return intval($_GET[$key]);
        return 0;
    }

    public static function anti_tamper_light() {
    // v1.4.0: placeholder.
    // v1.5.0+: we can scan for obvious hacks, compare hashes, etc.

    // future example : 
    // - detect if NH_License::is_pro() was manually hardcoded to always return true
    // - detect if pro files were modified
    // - if tampered => maybe mark nh_license_valid = 0

    return true;
}

}
