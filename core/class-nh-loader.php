<?php
// Loader: REST & Webhook activation with safety

if (!defined('ABSPATH')) exit;

class NH_Loader {

    protected $r; // NH_Core_Registry instance

    public function __construct($registry) {
        $this->r = $registry;
    }

    public function boot() {

        // === Shared services ============================================
        if (!$this->r->get_svc('notifier') && class_exists('NH_Notifier')) {
            $this->r->set('notifier', new NH_Notifier($this->r));
        }

        if (!$this->r->get_svc('license') && class_exists('NH_License')) {
            $this->r->set('license', new NH_License());
        }

        // === Admin UI / Dashboard / Hooks ==============================
        if (class_exists('NH_Admin_UI')) {
            if (method_exists('NH_Admin_UI', 'init')) {
                NH_Admin_UI::init($this->r);
            } else {
                new NH_Admin_UI($this->r);
            }
        }

        if (class_exists('NH_Dashboard') && method_exists('NH_Dashboard', 'init')) {
            NH_Dashboard::init($this->r);
        }

        if (class_exists('NH_Custom_Hooks') && method_exists('NH_Custom_Hooks', 'init')) {
            NH_Custom_Hooks::init($this->r);
        }

        // === Integrations ==============================================
        $integrations = ['NH_Int_WP_Core','NH_Int_WooCommerce','NH_Int_CF7'];
        $registry = is_object($this->r) ? $this->r : NH_Core_Registry::get();

        foreach ($integrations as $cls) {
            if (!class_exists($cls)) continue;
            try {
                if (method_exists($cls, 'init')) call_user_func([$cls, 'init'], $registry);
                else new $cls($registry);
            } catch (Throwable $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("❌ Integration $cls failed: " . $e->getMessage());
                }
            }
        }

        // === REST / Webhook ============================================
        global $wpdb;
        $table_hooks = $wpdb->prefix . 'nh_hooks';
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s", $wpdb->esc_like($table_hooks)
        ));

        if ($table_exists) {
            // ✅ Load REST API
            if (class_exists('NH_REST_API')) {
                try {
                    new NH_REST_API($this->r);
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('✅ NH_REST_API initialized successfully.');
                    }
                } catch (Throwable $e) {
                    error_log('❌ NH_REST_API failed: ' . $e->getMessage());
                }
            }

            // ✅ Load Webhook listener
            if (class_exists('NH_Webhook')) {
                try {
                    $wh = new NH_Webhook($this->r);
                    $wh->init();
                } catch (Throwable $e) {
                    error_log('❌ NH_Webhook failed: ' . $e->getMessage());
                }
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('⚠️ NH_Loader: Skipped REST/Webhook — nh_hooks table not found.');
            }
        }
    }
}
