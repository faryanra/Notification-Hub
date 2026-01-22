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
     * Ensure include helper exists.
     *
     * @since 1.7.2
     * @return void
     */
    protected static function ensure_include_helper(): void {
        if (!class_exists('NH_Include')) {
            $path = NH_PLUGIN_DIR . 'core/class-nh-include.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    /**
     * Save license key.
     *
     * @since 1.6.2
     */
    public static function save(): void {
        self::ensure_include_helper();

        if (!class_exists('NH_License_Action_Save_Key')) {
            if (class_exists('NH_Include')) {
                NH_Include::once('modules/license/admin/actions/save-key.php');
            } else {
                $path = NH_PLUGIN_DIR . 'modules/license/admin/actions/save-key.php';
                if (file_exists($path)) {
                    require_once $path;
                }
            }
        }

        if (class_exists('NH_License_Action_Save_Key')) {
            (new NH_License_Action_Save_Key())->handle();
            return;
        }

        wp_die(esc_html__('License action not available.', 'notification-hub'));
    }

    /**
     * Save license server URL.
     *
     * @since 1.7.0
     */
    public static function save_server(): void {
        self::ensure_include_helper();

        if (!class_exists('NH_License_Action_Save_Server')) {
            if (class_exists('NH_Include')) {
                NH_Include::once('modules/license/admin/actions/save-server.php');
            } else {
                $path = NH_PLUGIN_DIR . 'modules/license/admin/actions/save-server.php';
                if (file_exists($path)) {
                    require_once $path;
                }
            }
        }

        if (class_exists('NH_License_Action_Save_Server')) {
            (new NH_License_Action_Save_Server())->handle();
            return;
        }

        wp_die(esc_html__('License action not available.', 'notification-hub'));
    }

    /**
     * Save both license server URL + license key in one action.
     *
     * Enforces strict key format:
     * - NH-PRO-XXXX-XXXX
     * - X is A-Z0-9
     *
     * @since 1.7.0
     */
    public static function save_bundle(): void {
        self::ensure_include_helper();

        if (!class_exists('NH_License_Action_Save_Bundle')) {
            if (class_exists('NH_Include')) {
                NH_Include::once('modules/license/admin/actions/save-bundle.php');
            } else {
                $path = NH_PLUGIN_DIR . 'modules/license/admin/actions/save-bundle.php';
                if (file_exists($path)) {
                    require_once $path;
                }
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
        self::ensure_include_helper();

        if (!class_exists('NH_License_Action_Revoke')) {
            if (class_exists('NH_Include')) {
                NH_Include::once('modules/license/admin/actions/revoke.php');
            } else {
                $path = NH_PLUGIN_DIR . 'modules/license/admin/actions/revoke.php';
                if (file_exists($path)) {
                    require_once $path;
                }
            }
        }

        if (class_exists('NH_License_Action_Revoke')) {
            (new NH_License_Action_Revoke())->handle();
            return;
        }

        wp_die(esc_html__('License action not available.', 'notification-hub'));
    }
}
