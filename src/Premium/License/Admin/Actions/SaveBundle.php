<?php

namespace NotificationHub\Premium\License\Admin\Actions;

use NotificationHub\Premium\License\Policy\CapabilitiesPolicy;
use NotificationHub\Premium\License\Policy\DomainPolicy;
use NotificationHub\Premium\License\Policy\KeyFormatPolicy;
use NotificationHub\Premium\License\Services\LicenseService;
use NotificationHub\Premium\License\Storage\OptionStore;

/**
 * Save license bundle action (placeholder).
 *
 * @since 1.7.2
 */
final class SaveBundle {
    public function __invoke(): void {
        if (!(new CapabilitiesPolicy())->canManage()) {
            wp_die('Forbidden', 403);
        }

        check_admin_referer('nh_premium_save_bundle');

        $bundle = isset($_POST['bundle']) ? sanitize_textarea_field(wp_unslash($_POST['bundle'])) : '';

        $data = (new OptionStore())->get();
        $data['bundle'] = $bundle;
        $data['updated_at'] = time();
        (new OptionStore())->set($data);

        wp_safe_redirect(add_query_arg(['page' => 'nh_settings', 'tab' => 'premium', 'bundle_saved' => 1], admin_url('admin.php')));
        exit;
    }
}
