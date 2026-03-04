<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Helpers\Security;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Admin AJAX route: quick repository smoke test.
 *
 * POST: action=nh_repo_test&_wpnonce=...&mode=insert|query
 *
 * @since 1.7.2
 */
final class RepoTest {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $mode = isset($_POST['mode']) ? sanitize_key((string) wp_unslash($_POST['mode'])) : 'query';

        $repo = new NotificationsRepository();

        if ($mode === 'insert') {
            $id = $repo->insert([
                'source' => 'repo_test',
                'type' => 'smoke',
                'title' => 'Repo test',
                'message' => 'Inserted via repo test',
                'status' => 0,
                'priority' => 50,
                'tags' => ['repo_test','smoke'],
            ]);

            wp_send_json_success(['inserted_id' => $id]);
        }

        $res = $repo->queryForDashboard([
            'status_filter' => 'all',
            'per_page' => 5,
            'paged' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ]);

        wp_send_json_success($res);
    }
}
