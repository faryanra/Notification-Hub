<?php

namespace NotificationHub\Premium\License\Admin\Actions;

use NotificationHub\Premium\License\Policy\CapabilitiesPolicy;
use NotificationHub\Premium\License\Policy\DomainPolicy;
use NotificationHub\Premium\License\Policy\KeyFormatPolicy;
use NotificationHub\Premium\License\Services\LicenseService;
use NotificationHub\Premium\License\Storage\OptionStore;

/**
 * Save license key action.
 *
 * @since 1.7.2
 */
final class SaveKey {
    public function __invoke(): void {
        if (!(new CapabilitiesPolicy())->canManage()) {
            wp_die('Forbidden', 403);
        }

        check_admin_referer('nh_premium_save_key');

        $key = isset($_POST['key']) ? sanitize_text_field(wp_unslash($_POST['key'])) : '';

        $svc = new LicenseService(new OptionStore(), new KeyFormatPolicy(), new DomainPolicy());
        $res = $svc->saveKey($key);

        $args = ['page' => 'nh_settings', 'tab' => 'premium'];
        if (is_wp_error($res)) {
            $args['license_err'] = $res->get_error_code();
        } else {
            $args['license_saved'] = 1;
        }

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }
}
