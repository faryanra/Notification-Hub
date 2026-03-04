<?php

namespace NotificationHub\Premium\License\Admin\Actions;

use NotificationHub\Premium\License\Policy\CapabilitiesPolicy;
use NotificationHub\Premium\License\Policy\DomainPolicy;
use NotificationHub\Premium\License\Policy\KeyFormatPolicy;
use NotificationHub\Premium\License\Services\LicenseService;
use NotificationHub\Premium\License\Storage\OptionStore;

/**
 * Save license server action.
 *
 * @since 1.7.2
 */
final class SaveServer {
    public function __invoke(): void {
        if (!(new CapabilitiesPolicy())->canManage()) {
            wp_die('Forbidden', 403);
        }

        check_admin_referer('nh_premium_save_server');

        $server = isset($_POST['server']) ? esc_url_raw(wp_unslash($_POST['server'])) : '';

        $svc = new LicenseService(new OptionStore(), new KeyFormatPolicy(), new DomainPolicy());
        $ok  = $svc->saveServer($server);

        $args = ['page' => 'nh_settings', 'tab' => 'premium'];
        $args[$ok ? 'server_saved' : 'server_err'] = 1;

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }
}
