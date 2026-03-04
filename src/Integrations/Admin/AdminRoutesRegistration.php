<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Routes\Admin\ExportCsv;

/**
 * Register admin-post routes.
 *
 * @since 1.7.2
 */
final class AdminRoutesRegistration implements Integration {
    /**
     * Deprecated.
     *
     * Route registration moved to AdminPostRoutesRegistration to keep all
     * admin-post endpoints in one place.
     *
     * @since 1.7.2
     */
    public function register(Loader $loader): void {
        unset($loader);
    }

    /**
     * @since 1.7.2
     */
    public function exportCsv(): void {
        // Don't rely on autoloader for new tree files yet.
        if (!class_exists(ExportCsv::class)) {
            $file = NH_SRC_DIR . 'Routes/Admin/ExportCsv.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }

        if (!class_exists(ExportCsv::class)) {
            wp_die(esc_html__('Export route is missing.', 'notification-hub'));
        }

        (new ExportCsv())->handle();
    }
}
