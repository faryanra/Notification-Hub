<?php

namespace NotificationHub\Premium\License\Admin\Actions;

use NotificationHub\Premium\License\Policy\CapabilitiesPolicy;
use NotificationHub\Premium\License\Policy\DomainPolicy;
use NotificationHub\Premium\License\Policy\KeyFormatPolicy;
use NotificationHub\Premium\License\Services\LicenseService;
use NotificationHub\Premium\License\Storage\OptionStore;

/**
 * Revoke license action.
 *
 * @since 1.7.2
 */
final class Revoke {
    public function __invoke(): void {
        if (!(new CapabilitiesPolicy())->canManage()) {
            wp_die('Forbidden', 403);
        }

        check_admin_referer('nh_premium_revoke');

        $svc = new LicenseService(new OptionStore(), new KeyFormatPolicy(), new DomainPolicy());
        $svc->revoke();

        wp_safe_redirect(add_query_arg(['page' => 'nh_settings', 'tab' => 'premium', 'revoked' => 1], admin_url('admin.php')));
        exit;
    }
}
