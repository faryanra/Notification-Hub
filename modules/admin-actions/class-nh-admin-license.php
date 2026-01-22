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
        if (!class_exists('NH_License_Action_Save_Bundle')) {
            $path = NH_PLUGIN_DIR . 'modules/license/admin/actions/save-bundle.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_Action_Save_Bundle')) {
            (new NH_License_Action_Save_Bundle())->handle();
            return;
        }

        wp_die(esc_html__('License action not available.', 'notification-hub'));
    }

    /**
     * Revoke license key.
     *
     * @since 1.6.2
     */
    public static function revoke(): void {
        if (!class_exists('NH_License_Action_Revoke')) {
            $path = NH_PLUGIN_DIR . 'modules/license/admin/actions/revoke.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_Action_Revoke')) {
            (new NH_License_Action_Revoke())->handle();
            return;
        }

        wp_die(esc_html__('License action not available.', 'notification-hub'));
    }
}
