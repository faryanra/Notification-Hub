<?php
namespace NotificationHub\Repositories;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data access for automation rules (nh_rules table).
 *
 * @since 1.0.0
 */
final class RulesRepository {
    private function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'nh_rules';
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listAll(): array {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results('SELECT * FROM ' . $this->table() . ' ORDER BY enabled DESC, priority DESC, id DESC', ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listEnabledOrdered(): array {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results('SELECT * FROM ' . $this->table() . ' WHERE enabled=1 ORDER BY priority DESC, id ASC', ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getById(int $id): ?array {
        global $wpdb;

        $id = absint($id);
        if ($id <= 0) {
            return null;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $this->table() . ' WHERE id=%d', $id), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function create(string $name, bool $enabled, int $priority, string $conditionsJson, string $actionsJson): int {
        global $wpdb;

        $name = sanitize_text_field($name);
        if ($name === '') {
            return 0;
        }

        $now = current_time('mysql');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
            $this->table(),
            [
                'name'       => $name,
                'enabled'    => $enabled ? 1 : 0,
                'priority'   => (int) $priority,
                'conditions' => $conditionsJson,
                'actions'    => $actionsJson,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['%s', '%d', '%d', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    public function update(int $id, string $name, bool $enabled, int $priority, string $conditionsJson, string $actionsJson): bool {
        global $wpdb;

        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        $name = sanitize_text_field($name);
        if ($name === '') {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'name'       => $name,
                'enabled'    => $enabled ? 1 : 0,
                'priority'   => (int) $priority,
                'conditions' => $conditionsJson,
                'actions'    => $actionsJson,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s', '%d', '%d', '%s', '%s', '%s'],
            ['%d']
        );

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function delete(int $id): bool {
        global $wpdb;

        $id = absint($id);
        if ($id <= 0) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $deleted = $wpdb->delete($this->table(), ['id' => $id], ['%d']);
        return ($deleted !== false) && empty($wpdb->last_error);
    }

    public function duplicate(int $id): int {
        $row = $this->getById($id);
        if (!$row) {
            return 0;
        }

        $name = isset($row['name']) ? (string) $row['name'] : '';
        if ($name === '') {
            $name = 'Rule';
        }

        return $this->create(
            $name . ' (Copy)',
            !empty($row['enabled']),
            (int) ($row['priority'] ?? 0),
            isset($row['conditions']) ? (string) $row['conditions'] : '{}',
            isset($row['actions']) ? (string) $row['actions'] : '{}'
        );
    }
}


