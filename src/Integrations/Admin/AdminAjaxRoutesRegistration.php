<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Routes\Admin\BulkAction;
use NotificationHub\Routes\Admin\DeleteNotification;
use NotificationHub\Routes\Admin\DispatcherTest;
use NotificationHub\Routes\Admin\GetUnreadCount;
use NotificationHub\Routes\Admin\HooksRepoTest;
use NotificationHub\Routes\Admin\LoadNotification;
use NotificationHub\Routes\Admin\MarkNotificationImportant;
use NotificationHub\Routes\Admin\MarkNotificationRead;
use NotificationHub\Routes\Admin\MarkNotificationUnread;
use NotificationHub\Routes\Admin\QueueRepoTest;
use NotificationHub\Routes\Admin\RepoTest;
use NotificationHub\Routes\Admin\SettingsRepoTest;
use NotificationHub\Routes\Admin\UnmarkNotificationImportant;

/**
 * Register admin-ajax routes.
 *
 * @since 1.7.2
 */
final class AdminAjaxRoutesRegistration implements Integration {
    /**
     * @since 1.7.2
     */
    public function register(Loader $loader): void {
        $loader->addAction('wp_ajax_nh_view_notification', [$this, 'viewNotification']);
        $loader->addAction('wp_ajax_nh_mark_read', [$this, 'markRead']);
        $loader->addAction('wp_ajax_nh_mark_unread', [$this, 'markUnread']);
        $loader->addAction('wp_ajax_nh_mark_important', [$this, 'markImportant']);
        $loader->addAction('wp_ajax_nh_unmark_important', [$this, 'unmarkImportant']);
        $loader->addAction('wp_ajax_nh_delete_notification', [$this, 'deleteNotification']);
        $loader->addAction('wp_ajax_nh_get_unread_count', [$this, 'getUnreadCount']);
        $loader->addAction('wp_ajax_nh_bulk_action', [$this, 'bulkAction']);

        // Smoke tests should be available only in development.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $loader->addAction('wp_ajax_nh_repo_test', [$this, 'repoTest']);
            $loader->addAction('wp_ajax_nh_hooks_repo_test', [$this, 'hooksRepoTest']);
            $loader->addAction('wp_ajax_nh_queue_repo_test', [$this, 'queueRepoTest']);
            $loader->addAction('wp_ajax_nh_settings_repo_test', [$this, 'settingsRepoTest']);
            $loader->addAction('wp_ajax_nh_dispatcher_test', [$this, 'dispatcherTest']);
        }
    }

    public function viewNotification(): void {
        $this->requireRoute(LoadNotification::class, 'Routes/Admin/LoadNotification.php');
        (new LoadNotification())->handle();
    }

    public function markRead(): void {
        $this->requireRoute(MarkNotificationRead::class, 'Routes/Admin/MarkNotificationRead.php');
        (new MarkNotificationRead())->handle();
    }

    public function markUnread(): void {
        $this->requireRoute(MarkNotificationUnread::class, 'Routes/Admin/MarkNotificationUnread.php');
        (new MarkNotificationUnread())->handle();
    }

    public function markImportant(): void {
        $this->requireRoute(MarkNotificationImportant::class, 'Routes/Admin/MarkNotificationImportant.php');
        (new MarkNotificationImportant())->handle();
    }

    public function unmarkImportant(): void {
        $this->requireRoute(UnmarkNotificationImportant::class, 'Routes/Admin/UnmarkNotificationImportant.php');
        (new UnmarkNotificationImportant())->handle();
    }

    public function deleteNotification(): void {
        $this->requireRoute(DeleteNotification::class, 'Routes/Admin/DeleteNotification.php');
        (new DeleteNotification())->handle();
    }

    public function getUnreadCount(): void {
        $this->requireRoute(GetUnreadCount::class, 'Routes/Admin/GetUnreadCount.php');
        (new GetUnreadCount())->handle();
    }

    public function bulkAction(): void {
        $this->requireRoute(BulkAction::class, 'Routes/Admin/BulkAction.php');
        (new BulkAction())->handle();
    }

    public function repoTest(): void {
        $this->requireRoute(RepoTest::class, 'Routes/Admin/RepoTest.php');
        (new RepoTest())->handle();
    }

    public function hooksRepoTest(): void {
        $this->requireRoute(HooksRepoTest::class, 'Routes/Admin/HooksRepoTest.php');
        (new HooksRepoTest())->handle();
    }

    public function queueRepoTest(): void {
        $this->requireRoute(QueueRepoTest::class, 'Routes/Admin/QueueRepoTest.php');
        (new QueueRepoTest())->handle();
    }

    public function settingsRepoTest(): void {
        $this->requireRoute(SettingsRepoTest::class, 'Routes/Admin/SettingsRepoTest.php');
        (new SettingsRepoTest())->handle();
    }

    public function dispatcherTest(): void {
        $this->requireRoute(DispatcherTest::class, 'Routes/Admin/DispatcherTest.php');
        (new DispatcherTest())->handle();
    }

    /**
     * @since 1.7.2
     */
    private function requireRoute(string $class, string $relPath): void {
        if (!class_exists($class)) {
            $file = NH_SRC_DIR . $relPath;
            if (file_exists($file)) {
                require_once $file;
            }
        }

        if (!class_exists($class)) {
            wp_send_json_error(['message' => esc_html__('Route is missing.', 'notification-hub')], 500);
        }
    }
}
