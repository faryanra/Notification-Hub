<?php
/**
 * NH_Admin_License
 *
 * License management (save/revoke) + license server URL.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Admin_License {

    /**
     * Register license action handlers.
     *
     * @since 1.6.2
     */
    public static function init(): void {
        add_action('admin_post_nh_save_license', [__CLASS__, 'save']);
        add_action('admin_post_nh_license_revoke', [__CLASS__, 'revoke']);
        add_action('admin_post_nh_save_license_server', [__CLASS__, 'save_server']);

        // New unified handler for the redesigned UI.
        add_action('admin_post_nh_save_license_bundle', [__CLASS__, 'save_bundle']);
    }

    /**
     * Save license key.
     *
     * @since 1.6.2
     */
    public static function save(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_save_license');

        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';

        if (class_exists('NH_License')) {
            NH_License::save_key($key);

            // Legacy behavior for 1.6.x: mark as valid when non-empty.
            if (method_exists('NH_License', 'set_valid')) {
                NH_License::set_valid($key !== '');
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_saved=1'));
        exit;
    }

    /**
     * Save license server URL.
     *
     * @since 1.7.0
     */
    public static function save_server(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_save_license_server');

        $url = isset($_POST['nh_license_server_url']) ? esc_url_raw(wp_unslash($_POST['nh_license_server_url'])) : '';

        if (class_exists('NH_License') && defined('NH_License::OPT_SERVER_URL')) {
            update_option(NH_License::OPT_SERVER_URL, $url, false);

            // If server URL changes, reset state so next load re-checks.
            if (method_exists('NH_License', 'reset_state')) {
                NH_License::reset_state();
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=nh_settings&nh_license_server_saved=1'));
        exit;
    }

    /**
     * Save both license server URL + license key in one action.
     *
     * Enforces strict key format:
     * - NH-PRO-XXXX-XXXX
     * - X is A-Z or 0-9
     *
     * @since 1.7.0
     */
    public static function save_bundle(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_save_license_bundle');

        $url = isset($_POST['nh_license_server_url']) ? esc_url_raw(wp_unslash($_POST['nh_license_server_url'])) : '';
        $key = isset($_POST['nh_license_key']) ? sanitize_text_field(wp_unslash($_POST['nh_license_key'])) : '';
        $key = strtoupper(trim((string) $key));

        // IMPORTANT: Use the same tab slug used by the UI (premium).
        $redirect_base = 'admin.php?page=nh_settings&tab=premium';

        // Validate only when user attempts to submit a key.
        if ($key !== '' && !preg_match('/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key)) {
            wp_safe_redirect(admin_url($redirect_base . '&nh_license_error=invalid_key'));
            exit;
        }

        if (class_exists('NH_License')) {
            // Save server url (optional).
            if (defined('NH_License::OPT_SERVER_URL')) {
                update_option(NH_License::OPT_SERVER_URL, $url, false);
            }

            // Save key (optional).
            if (method_exists('NH_License', 'save_key')) {
                NH_License::save_key($key);
            }

            // Reset state so it re-checks immediately on next load.
            if (method_exists('NH_License', 'reset_state')) {
                NH_License::reset_state();
            }

            // Try to refresh state now (best effort).
            if (method_exists('NH_License', 'maybe_refresh')) {
                NH_License::maybe_refresh();
            }
        }

        wp_safe_redirect(admin_url($redirect_base . '&nh_license_saved=1'));
        exit;
    }

    /**
     * Revoke license key.
     *
     * @since 1.6.2
     */
    public static function revoke(): void {
        if (!class_exists('NH_Security') || !method_exists('NH_Security', 'ensure_cap')) {
            wp_die(esc_html__('Security module not available.', 'notification-hub'));
        }

        NH_Security::ensure_cap();
        check_admin_referer('nh_license_revoke');

        if (class_exists('NH_License') && method_exists('NH_License', 'revoke')) {
            NH_License::revoke();
        }

        // Keep user on the Premium tab.
        wp_safe_redirect(admin_url('admin.php?page=nh_settings&tab=premium&nh_license_revoked=1'));
        exit;
    }
}