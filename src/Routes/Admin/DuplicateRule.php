<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\RulesRepository;
use NotificationHub\Security\Capabilities;

/**
 * Admin-post: duplicate automation rule.
 *
 * @since 1.0.0
 */
final class DuplicateRule {
    public function handle(): void {
        Capabilities::ensureManageOptions();

        $id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
        if ($id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=nh-rules&rule_duplicated=0&rule_error=missing_id'));
            exit;
        }

        check_admin_referer('nh_duplicate_rule_' . $id);

        $newId = (new RulesRepository())->duplicate($id);
        wp_safe_redirect(admin_url('admin.php?page=nh-rules&rule_duplicated=' . ($newId > 0 ? '1' : '0')));
        exit;
    }
}


