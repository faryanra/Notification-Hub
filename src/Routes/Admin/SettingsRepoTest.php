<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Helpers\Security;
use NotificationHub\Repositories\SettingsRepository;

/**
 * Admin AJAX route: settings repository smoke test.
 *
 * POST: action=nh_settings_repo_test&_wpnonce=...&mode=get|set
 *
 * @since 1.0.0
 */
final class SettingsRepoTest {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $mode = isset($_POST['mode']) ? sanitize_key((string) wp_unslash($_POST['mode'])) : 'get';
        $repo = new SettingsRepository();

        if ($mode === 'set') {
            $repo->updateGeneral([
                'retention_days' => 91,
            ]);
        }

        wp_send_json_success([
            'general' => $repo->getGeneral(),
            'channels' => $repo->getChannels(),
        ]);
    }
}

