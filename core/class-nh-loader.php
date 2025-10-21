<?php
// NH v1.2.0 — Loader: wire modules & free+pro integrations

if (!defined('ABSPATH')) exit;

require_once NH_PLUGIN_DIR.'modules/class-nh-admin-ui.php';
require_once NH_PLUGIN_DIR.'modules/class-nh-dashboard.php';
require_once NH_PLUGIN_DIR.'modules/class-nh-notifier.php';
require_once NH_PLUGIN_DIR.'modules/class-nh-custom-hooks.php';

require_once NH_PLUGIN_DIR.'integrations/class-nh-email.php';
require_once NH_PLUGIN_DIR.'integrations/class-nh-telegram.php';
require_once NH_PLUGIN_DIR.'integrations/class-nh-slack.php';
require_once NH_PLUGIN_DIR.'integrations/class-nh-wp-core.php';
require_once NH_PLUGIN_DIR.'integrations/class-nh-woocommerce.php';
require_once NH_PLUGIN_DIR.'integrations/class-nh-cf7.php';

class NH_Loader {
    protected $r;

    public function __construct($registry) { $this->r = $registry; }

    public function boot() {
        // NH v1.2.0 — Modules
        (new NH_Admin_UI($this->r))->hooks();
        (new NH_Dashboard($this->r))->hooks();
        (new NH_Custom_Hooks($this->r))->hooks();

        // NH v1.2.0 — Notifier with Free+Pro channels
        $this->r->set('notifier', new NH_Notifier($this->r));
        // NH v1.2.0 — Integrations
        (new NH_Int_WP_Core($this->r))->hooks();
        (new NH_Int_WooCommerce($this->r))->hooks();
        (new NH_Int_CF7($this->r))->hooks();
    }
}
