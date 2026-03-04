<?php

namespace NotificationHub\Premium\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Premium\License\Admin\Actions\Revoke;
use NotificationHub\Premium\License\Admin\Actions\SaveBundle;
use NotificationHub\Premium\License\Admin\Actions\SaveKey;
use NotificationHub\Premium\License\Admin\Actions\SaveServer;

/**
 * Registers premium admin-post actions.
 *
 * @since 1.7.2
 */
final class AdminPostRegistration implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('admin_post_nh_premium_save_key', new SaveKey());
        $loader->addAction('admin_post_nh_premium_save_server', new SaveServer());
        $loader->addAction('admin_post_nh_premium_save_bundle', new SaveBundle());
        $loader->addAction('admin_post_nh_premium_revoke', new Revoke());
    }
}
