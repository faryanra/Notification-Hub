<?php

namespace NotificationHub\Repositories;

/**
 * Data access for custom hooks (nh_hooks table).
 *
 * @since 1.7.2
 */
final class CustomHooksRepository {
    private function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'nh_hooks';
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listActive(): array {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results('SELECT * FROM ' . $this->table() . ' WHERE status=1', ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getById(int $id): ?array {
        global $wpdb;
        $id = absint($id);
        if (!$id) {
            return null;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $this->table() . ' WHERE id=%d', $id), ARRAY_A);
        return $row ?: null;
    }

    /**
     * Create a hook.
     *
     * @param string $title
     * @param string $actionName Must be validated by caller.
     * @param array<int,string> $channels
     * @return int Inserted ID (0 on failure)
     */
    public function create(string $title, string $actionName, array $channels = []): int {
        global $wpdb;

        $title = sanitize_text_field($title);
        $actionName = sanitize_text_field($actionName);

        if ($title === '' || $actionName === '') {
            return 0;
        }

        $channels = array_values(array_unique(array_filter(array_map('sanitize_key', $channels))));
        $channelsJson = $channels ? wp_json_encode($channels) : null;

        // Prevent duplicates by action_name.
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $exists = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $this->table() . ' WHERE action_name=%s', $actionName));
        if ($exists > 0) {
            return 0;
        }

        $wpdb->insert(
            $this->table(),
            [
                'title' => $title,
                'action_name' => $actionName,
                'channels' => $channelsJson,
                'status' => 1,
            ],
            ['%s','%s','%s','%d']
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * Update an existing hook.
     *
     * @param int $id
     * @param string $title
     * @param string $actionName Must be validated by caller.
     * @param array<int,string> $channels
     * @return bool
     */
    public function update(int $id, string $title, string $actionName, array $channels = []): bool {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return false;
        }

        $title = sanitize_text_field($title);
        $actionName = sanitize_text_field($actionName);

        if ($title === '' || $actionName === '') {
            return false;
        }

        // Prevent duplicates (same action_name on other rows).
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $dup = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $this->table() . ' WHERE action_name=%s AND id<>%d', $actionName, $id));
        if ($dup > 0) {
            return false;
        }

        $channels = array_values(array_unique(array_filter(array_map('sanitize_key', $channels))));
        $channelsJson = $channels ? wp_json_encode($channels) : null;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update(
            $this->table(),
            [
                'title' => $title,
                'action_name' => $actionName,
                'channels' => $channelsJson,
            ],
            ['id' => $id],
            ['%s','%s','%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public function delete(int $id): bool {
        global $wpdb;
        $id = absint($id);
        if (!$id) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $deleted = $wpdb->delete($this->table(), ['id' => $id], ['%d']);
        return $deleted !== false && $deleted > 0;
    }
}
