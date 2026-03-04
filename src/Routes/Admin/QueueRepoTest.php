<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Helpers\Security;
use NotificationHub\Repositories\QueueRepository;

/**
 * Admin AJAX route: queue settings smoke test.
 *
 * POST: action=nh_queue_repo_test&_wpnonce=...&mode=get|disable_localhost
 *
 * @since 1.7.2
 */
final class QueueRepoTest {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $mode = isset($_POST['mode']) ? sanitize_key((string) wp_unslash($_POST['mode'])) : 'get';
        $repo = new QueueRepository();

        if ($mode === 'disable_localhost') {
            $repo->update(['localhost_immediate' => false]);
        }

        wp_send_json_success(['settings' => $repo->get()]);
    }
}
