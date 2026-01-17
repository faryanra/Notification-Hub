<?php
/**
 * NH_Security
 *
 * Security helper utilities for admin actions (capability checks, nonces, sanitization).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Security {

    /**
     * Ensure current user can manage plugin settings.
     *
     * Use this at the top of admin_post / admin_action handlers.
     *
     * @since 1.6.2
     * @return void
     */
    public static function ensure_cap(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'notification-hub'));
        }
    }

    /**
     * Verify a request nonce.
     *
     * Nonce field must be created using:
     * - NH_Security::nonce_field('nh_save_hook');
     * - NH_Security::nonce_field('nh_update_hook', $id);
     *
     * This method expects the nonce to be sent in `$_REQUEST['_wpnonce']`.
     *
     * @since 1.6.2
     * @param string $action_base Action base string (e.g. 'nh_save_hook').
     * @param int    $id          Optional numeric context (e.g. hook ID).
     * @return void
     */
    public static function verify_nonce(string $action_base, int $id = 0): void {
        $nonce = isset($_REQUEST['_wpnonce'])
            ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']))
            : '';

        $action = $id ? ($action_base . '_' . $id) : $action_base;

        if (!$nonce || !wp_verify_nonce($nonce, $action)) {
            wp_die(
                esc_html__(
                    'Invalid request (nonce). Please refresh and try again.',
                    'notification-hub'
                )
            );
        }
    }

    /**
     * Output nonce field for use in forms.
     *
     * @since 1.6.2
     * @param string $action_base Action base string (e.g. 'nh_save_hook').
     * @param int    $id          Optional numeric context (e.g. hook ID).
     * @return void
     */
    public static function nonce_field(string $action_base, int $id = 0): void {
        $action = $id ? ($action_base . '_' . $id) : $action_base;
        wp_nonce_field($action);
    }

    /**
     * Sanitize & normalize list of selected channels.
     *
     * @since 1.6.2
     * @param mixed $maybe_channels Channels array from request.
     * @return array<string> Clean channel slugs.
     */
    public static function sanitize_channels($maybe_channels): array {
        if (!is_array($maybe_channels)) {
            return [];
        }

        $clean = [];
        foreach ($maybe_channels as $ch) {
            $s = sanitize_text_field((string) $ch);
            if ($s !== '') {
                $clean[] = $s;
            }
        }

        return $clean;
    }

    /**
     * Validate a custom hook action name for do_action().
     *
     * @since 1.6.2
     * @param string $raw Raw action name.
     * @return string Sanitized action name.
     */
    public static function validate_action_name(string $raw): string {
        $raw = sanitize_text_field($raw);
        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $raw);
    }

    /**
     * Safely pull an integer from POST or GET.
     *
     * @since 1.6.2
     * @param string $key Request key.
     * @return int Parsed integer.
     */
    public static function request_int(string $key): int {
        if (isset($_POST[$key])) {
            return (int) wp_unslash($_POST[$key]);
        }
        if (isset($_GET[$key])) {
            return (int) wp_unslash($_GET[$key]);
        }
        return 0;
    }

    /**
     * Anti-tamper placeholder (light).
     *
     * @since 1.6.2
     * @return bool Always true for now.
     */
    public static function anti_tamper_light(): bool {
        return true;
    }
}
