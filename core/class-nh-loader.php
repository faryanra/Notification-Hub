<?php
// Loader
// Wires registry services into admin UI, integrations, REST API, etc.

if (!defined('ABSPATH')) {
    exit;
}

class NH_Loader {

    protected $r; // NH_Core_Registry instance

    public function __construct($registry) {
        $this->r = $registry;
    }

    /**
     * boot()
     * - expose shared services (notifier, license)
     * - init admin UI modules
     * - init integrations
     * - init REST / webhook layer
     */
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
                // fallback: some versions register menus via constructor
                new NH_Admin_UI($this->r);
            }
        }

        if (class_exists('NH_Dashboard')) {
            if (method_exists('NH_Dashboard', 'init')) {
                NH_Dashboard::init($this->r);
            }
        }

        if (class_exists('NH_Custom_Hooks')) {
            if (method_exists('NH_Custom_Hooks', 'init')) {
                NH_Custom_Hooks::init($this->r);
            }
        }

        // NH_Admin_Actions already hooked via admin_init inside the class file.
        if (!class_exists('NH_Admin_Actions')) {
            error_log('Notification Hub: NH_Admin_Actions missing');
        }

        // === Integrations ==============================================
        $integrations = [
            'NH_Int_WP_Core',
            'NH_Int_WooCommerce',
            'NH_Int_CF7',
        ];

        $registry = is_object($this->r) ? $this->r : NH_Core_Registry::get();

        foreach ($integrations as $cls) {
            if (!class_exists($cls)) continue;

            try {
                // If class has static init()
                if (method_exists($cls, 'init')) {
                    call_user_func([$cls, 'init'], $registry);
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("✅ $cls initialized via ::init()");
                    }
                }
                // Else if it has a constructor
                elseif (method_exists($cls, '__construct')) {
                    new $cls($registry);
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("✅ $cls initialized via constructor");
                    }
                }
                // If neither found, log warning
                else {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("⚠️ $cls has no init() or constructor — skipped");
                    }
                }
            } catch (Throwable $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("❌ Integration load failed for $cls: " . $e->getMessage());
                }
            }
        }

        // === API / Webhook =============================================
        // REST API route registration
        if (class_exists('NH_REST_API')) {
            // allow both __construct($r) or no-arg __construct()
            try {
                $api = (new ReflectionClass('NH_REST_API'));
                $ctor = $api->getConstructor();
                if ($ctor && $ctor->getNumberOfParameters() > 0) {
                    $api->newInstance($this->r);
                } else {
                    $api->newInstance();
                }
            } catch (Throwable $e) {
                error_log('Notification Hub: NH_REST_API init failed: ' . $e->getMessage());
            }
        } else {
            error_log('Notification Hub: NH_REST_API missing');
        }

        // inbound webhooks placeholder for future
        if (class_exists('NH_Webhook')) {
            try {
                $wh = (new ReflectionClass('NH_Webhook'));
                $ctor = $wh->getConstructor();
                if ($ctor && $ctor->getNumberOfParameters() > 0) {
                    $wh->newInstance($this->r);
                } else {
                    $wh->newInstance();
                }
            } catch (Throwable $e) {
                error_log('Notification Hub: NH_Webhook init failed: ' . $e->getMessage());
            }
        }
    }
}
