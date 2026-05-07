<?php
namespace NotificationHub\Routes\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\RulesRepository;
use NotificationHub\Security\Capabilities;

/**
 * Admin-post: update automation rule.
 *
 * @since 1.0.0
 */
final class UpdateRule {
    public function handle(): void {
        Capabilities::ensureManageOptions();

        $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
        if ($id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=nh-rules&rule_updated=0&rule_error=missing_id'));
            exit;
        }

        check_admin_referer('nh_update_rule_' . $id);

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $enabled = !empty($_POST['enabled']);
        $priority = isset($_POST['priority']) ? (int) wp_unslash($_POST['priority']) : 100;
        $conditionsRaw = isset($_POST['conditions']) ? (string) wp_unslash($_POST['conditions']) : '';
        $actionsRaw = isset($_POST['actions']) ? (string) wp_unslash($_POST['actions']) : '';

        if ($name === '') {
            wp_safe_redirect(admin_url('admin.php?page=nh-rules&edit=' . $id . '&rule_updated=0&rule_error=missing_name'));
            exit;
        }

        $conditions = $this->decodeJson($conditionsRaw);
        $actions = $this->decodeJson($actionsRaw);
        if ($conditions === null || $actions === null) {
            wp_safe_redirect(admin_url('admin.php?page=nh-rules&edit=' . $id . '&rule_updated=0&rule_error=invalid_json'));
            exit;
        }

        $repo = new RulesRepository();
        $ok = $repo->update(
            $id,
            $name,
            $enabled,
            $priority,
            wp_json_encode($conditions),
            wp_json_encode($actions)
        );

        wp_safe_redirect(admin_url('admin.php?page=nh-rules&rule_updated=' . ($ok ? '1' : '0')));
        exit;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function decodeJson(string $json): ?array {
        $json = trim($json);
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }
}


