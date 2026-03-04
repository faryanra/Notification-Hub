<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Presenters\Admin\HooksPage;
use NotificationHub\Presenters\Admin\SettingsPage;

/**
 * Register admin menu (legacy-identical behavior).
 *
 * @since 1.7.2
 */
final class MenuRegistration implements Integration {
    /**
     * @since 1.7.2
     */
    public function register(Loader $loader): void {
        $loader->addAction('admin_menu', [$this, 'registerMenus']);
    }

    /**
     * Register admin menus.
     *
     * Matches legacy slugs/order/position:
     * - parent: nh-dashboard (position 58)
     * - sub: nh-dashboard, nh-hooks, nh_settings
     *
     * @since 1.7.2
     * @return void
     */
    public function registerMenus(): void {
        add_menu_page(
            esc_html__('Notification Hub', 'notification-hub'),
            esc_html__('Notification Hub', 'notification-hub'),
            'manage_options',
            'nh-dashboard',
            [$this, 'renderDashboard'],
            'dashicons-bell',
            58
        );

        add_submenu_page(
            'nh-dashboard',
            esc_html__('Dashboard', 'notification-hub'),
            esc_html__('Dashboard', 'notification-hub'),
            'manage_options',
            'nh-dashboard',
            [$this, 'renderDashboard']
        );

        add_submenu_page(
            'nh-dashboard',
            esc_html__('Hooks', 'notification-hub'),
            esc_html__('Hooks', 'notification-hub'),
            'manage_options',
            'nh-hooks',
            [$this, 'renderHooks']
        );

        add_submenu_page(
            'nh-dashboard',
            esc_html__('Settings', 'notification-hub'),
            esc_html__('Settings', 'notification-hub'),
            'manage_options',
            'nh_settings',
            [$this, 'renderSettings']
        );
    }

    /**
     * Ensure legacy (global) dashboard classes are loaded from the new tree.
     *
     * @since 1.7.2
     */
    private function ensureDashboardClassesLoaded(): void {
        if (!class_exists('NH_Dashboard')) {
            $dashboard_file = NH_SRC_DIR . 'Presenters/Admin/DashboardPage.php';
            if (file_exists($dashboard_file)) {
                require_once $dashboard_file;
            }
        }

        if (!class_exists('NH_Notifications_Table')) {
            $table_file = NH_SRC_DIR . 'Presenters/Admin/NotificationsListTable.php';
            if (file_exists($table_file)) {
                require_once $table_file;
            }
        }
    }

    /**
     * Render dashboard page.
     *
     * @since 1.7.2
     */
    public function renderDashboard(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'notification-hub'));
        }

        $this->ensureDashboardClassesLoaded();

        if (class_exists('NH_Dashboard')) {
            $dashboard = new \NH_Dashboard(null);
            $dashboard->render();
        } else {
            echo '<div class="wrap"><p>' . esc_html__('Dashboard class is missing.', 'notification-hub') . '</p></div>';
        }

        $modal_file = NH_PLUGIN_DIR . 'templates/admin/modal-preview.php';
        if (file_exists($modal_file)) {
            include $modal_file;
        }
    }

    /**
     * Render hooks page.
     *
     * @since 1.7.2
     */
    public function renderHooks(): void {
        $this->ensurePresenterLoaded(HooksPage::class, 'Presenters/Admin/HooksPage.php');

        if (class_exists(HooksPage::class)) {
            (new HooksPage())->render();
            return;
        }

        echo '<div class="wrap"><h1>' . esc_html__('Hooks', 'notification-hub') . '</h1><p>' . esc_html__('Presenter is missing.', 'notification-hub') . '</p></div>';
    }

    /**
     * Render settings page.
     *
     * @since 1.7.2
     */
    public function renderSettings(): void {
        $this->ensurePresenterLoaded(SettingsPage::class, 'Presenters/Admin/SettingsPage.php');

        if (class_exists(SettingsPage::class)) {
            (new SettingsPage())->render();
            return;
        }

        echo '<div class="wrap"><h1>' . esc_html__('Settings', 'notification-hub') . '</h1><p>' . esc_html__('Presenter is missing.', 'notification-hub') . '</p></div>';
    }

    private function ensurePresenterLoaded(string $class, string $relPath): void {
        if (!class_exists($class)) {
            $file = NH_SRC_DIR . $relPath;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}
