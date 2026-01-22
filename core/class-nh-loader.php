<?php
/**
 * NH_Loader
 *
 * Boots Notification Hub services, admin modules, integrations, and API layers.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Loader {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var NH_Core_Registry
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param NH_Core_Registry $registry Registry instance.
     */
    public function __construct($registry) {
        $this->r = $registry;
    }

    /**
     * Boot all plugin components.
     *
     * @since 1.6.2
     * @return void
     */
    public function boot() {

        // -----------------------------------------
        // Centralized loading of Premium-only files.
        // -----------------------------------------
        // Rule: no pro/premium folders; Premium files are identified by filename prefix.
        // Premium addon presence is controlled by NH_PRO_ACTIVE.
        if (defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE) {
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
        }

        /**
         * Shared services.
         */
        if (!$this->r->get_svc('notifier') && class_exists('NH_Notifier')) {
            $this->r->set('notifier', new NH_Notifier($this->r));
        }

        if (class_exists('NH_Queue')) {
            NH_Queue::hook_processor($this->r);
        }

        if (!$this->r->get_svc('license') && class_exists('NH_License')) {
            $this->r->set('license', new NH_License());
        }

        /**
         * Admin UI / Dashboard / Hooks.
         */
        if (is_admin()) {
            if (class_exists('NH_Admin_UI')) {
                // Standard v1.6.2 boot style: constructor registers hooks.
                new NH_Admin_UI($this->r);
            }

            if (class_exists('NH_Dashboard') && method_exists('NH_Dashboard', 'init')) {
                NH_Dashboard::init($this->r);
            }

            if (class_exists('NH_Custom_Hooks') && method_exists('NH_Custom_Hooks', 'init')) {
                NH_Custom_Hooks::init($this->r);
            }

            // Premium admin actions.
            if (defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE) {
                if (class_exists('NH_Admin_License') && method_exists('NH_Admin_License', 'init')) {
                    NH_Admin_License::init();
                }
            }
        }

        /**
         * Integrations.
         */
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
                if (method_exists($cls, 'init')) {
                    call_user_func([$cls, 'init'], $this->r);
                } else {
                    new $cls($this->r);
                }
            } catch (Throwable $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log(sprintf('Notification Hub: Integration %s failed: %s', $cls, $e->getMessage()));
                }
            }
        }

        /**
         * REST / Webhook.
         * Only boot when nh_hooks table exists.
         */
        if (!$this->nh_hooks_table_exists()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Notification Hub: Skipped REST/Webhook — nh_hooks table not found.');
            }
            return;
        }

        if (class_exists('NH_REST_API')) {
            try {
                new NH_REST_API($this->r);
            } catch (Throwable $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log(sprintf('Notification Hub: NH_REST_API failed: %s', $e->getMessage()));
                }
            }
        }

        if (class_exists('NH_Webhook')) {
            try {
                $wh = new NH_Webhook($this->r);
                $wh->init();
            } catch (Throwable $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log(sprintf('Notification Hub: NH_Webhook failed: %s', $e->getMessage()));
                }
            }
        }
    }

    /**
     * Check if nh_hooks table exists.
     *
     * @since 1.6.2
     * @return bool
     */
    private function nh_hooks_table_exists() {
        global $wpdb;

        if (!isset($wpdb) || empty($wpdb->prefix)) {
            return false;
        }

        $table_hooks  = $wpdb->prefix . 'nh_hooks';
        $like_pattern = $wpdb->esc_like($table_hooks);

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $sql = $wpdb->prepare('SHOW TABLES LIKE %s', $like_pattern);

        return (bool) $wpdb->get_var($sql);
    }
}
