<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Routes\Admin\DeleteCustomHook;
use NotificationHub\Routes\Admin\ExportCsv;
use NotificationHub\Routes\Admin\RevokeLicense;
use NotificationHub\Routes\Admin\SaveCustomHook;
use NotificationHub\Routes\Admin\SaveLicenseBundle;
use NotificationHub\Routes\Admin\SaveLicenseKey;
use NotificationHub\Routes\Admin\SaveLicenseServer;
use NotificationHub\Routes\Admin\TestChannel;
use NotificationHub\Routes\Admin\TriggerCustomHook;
use NotificationHub\Routes\Admin\UpdateCustomHook;

/**
 * Registers admin-post routes.
 *
 * @since 1.7.2
 */
final class AdminPostRoutesRegistration implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('admin_post_nh_test_channel', [new TestChannel(), 'handle']);
        $loader->addAction('admin_post_nh_license_test', [new TestChannel(), 'handleLegacy']);
        $loader->addAction('admin_post_nh_export_csv', [new ExportCsv(), 'handle']);

        // Custom hooks (admin-post).
        $loader->addAction('admin_post_nh_save_hook', [new SaveCustomHook(), 'handle']);
        $loader->addAction('admin_post_nh_update_hook', [new UpdateCustomHook(), 'handle']);
        $loader->addAction('admin_post_nh_delete_hook', [new DeleteCustomHook(), 'handle']);
        $loader->addAction('admin_post_nh_test_hook', [new TriggerCustomHook(), 'handle']);

        // License admin actions.
        $loader->addAction('admin_post_nh_save_license_bundle', [new SaveLicenseBundle(), 'handle']);
        $loader->addAction('admin_post_nh_license_revoke', [new RevokeLicense(), 'handle']);

        // Legacy compat actions (may still be used by older UI templates).
        $loader->addAction('admin_post_nh_save_license', [new SaveLicenseKey(), 'handle']);
        $loader->addAction('admin_post_nh_save_license_server', [new SaveLicenseServer(), 'handle']);
    }
}
