<?php
namespace NotificationHub\Integrations\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Routes\Admin\DeleteCustomHook;
use NotificationHub\Routes\Admin\DeleteRule;
use NotificationHub\Routes\Admin\DuplicateRule;
use NotificationHub\Routes\Admin\ExportCsv;
use NotificationHub\Routes\Admin\ExportMetricsCsv;
use NotificationHub\Routes\Admin\SaveCustomHook;
use NotificationHub\Routes\Admin\SaveRule;
use NotificationHub\Routes\Admin\TestChannel;
use NotificationHub\Routes\Admin\TriggerCustomHook;
use NotificationHub\Routes\Admin\UpdateCustomHook;
use NotificationHub\Routes\Admin\UpdateRule;

/**
 * Registers admin-post routes.
 *
 * @since 1.0.0
 */
final class AdminPostRoutesRegistration implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('admin_post_nh_test_channel', [new TestChannel(), 'handle']);
        $loader->addAction('admin_post_nh_export_csv', [new ExportCsv(), 'handle']);
        $loader->addAction('admin_post_nh_export_metrics', [new ExportMetricsCsv(), 'handle']);

        // Custom hooks (admin-post).
        $loader->addAction('admin_post_nh_save_hook', [new SaveCustomHook(), 'handle']);
        $loader->addAction('admin_post_nh_update_hook', [new UpdateCustomHook(), 'handle']);
        $loader->addAction('admin_post_nh_delete_hook', [new DeleteCustomHook(), 'handle']);
        $loader->addAction('admin_post_nh_test_hook', [new TriggerCustomHook(), 'handle']);

        // Automation rules (admin-post).
        $loader->addAction('admin_post_nh_save_rule', [new SaveRule(), 'handle']);
        $loader->addAction('admin_post_nh_update_rule', [new UpdateRule(), 'handle']);
        $loader->addAction('admin_post_nh_delete_rule', [new DeleteRule(), 'handle']);
        $loader->addAction('admin_post_nh_duplicate_rule', [new DuplicateRule(), 'handle']);

    }
}

