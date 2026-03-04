<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Helpers\Security;
use NotificationHub\Repositories\CustomHooksRepository;

/**
 * Admin AJAX route: quick custom hooks repository smoke test.
 *
 * POST: action=nh_hooks_repo_test&_wpnonce=...&mode=create|list
 *
 * @since 1.7.2
 */
final class HooksRepoTest {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $mode = isset($_POST['mode']) ? sanitize_key((string) wp_unslash($_POST['mode'])) : 'list';
        $repo = new CustomHooksRepository();

        if ($mode === 'create') {
            $action = 'nh_custom_test_' . wp_generate_password(6, false, false);
            $id = $repo->create('Test Hook', $action, ['email']);
            wp_send_json_success(['created_id' => $id, 'action_name' => $action]);
        }

        $active = $repo->listActive();
        wp_send_json_success(['active' => $active]);
    }
}
