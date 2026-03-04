<?php

namespace NotificationHub\Repositories;

use NotificationHub\Services\PriorityCalculator;
use NotificationHub\Services\ServiceFactory;

/**
 * Data access for notifications.
 *
 * @since 1.7.2
 */
final class NotificationsRepository {
    /**
     * Hidden internal notification types for dashboard/count UX.
     *
     * @var array<int,string>
     */
    private const HIDDEN_TYPES = ['dispatch_check', 'email_sent'];

    private function hiddenTypesSql(): string {
        $types = array_map('sanitize_key', self::HIDDEN_TYPES);
        $quoted = array_map(static function (string $type): string {
            return "'" . esc_sql($type) . "'";
        }, $types);

        return 'type NOT IN (' . implode(', ', $quoted) . ')';
    }

    /**
     * @return string
     */
    private function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'nh_notifications';
    }

    /**
     * Insert a notification.
     *
     * @param array<string,mixed> $e
     * @return int Inserted ID (0 on failure)
     */
    public function insert(array $e): int {
        global $wpdb;

        $now = current_time('mysql');

        $source  = isset($e['source']) ? sanitize_text_field((string) $e['source']) : '';
        $type    = isset($e['type']) ? sanitize_text_field((string) $e['type']) : '';
        $title   = isset($e['title']) ? sanitize_text_field((string) $e['title']) : '';
        $message = isset($e['message']) ? (string) $e['message'] : '';

        if ($source === '' || $type === '' || $title === '' || $message === '') {
            return 0;
        }

        $priority = array_key_exists('priority', $e) ? (int) $e['priority'] : null;
        if ($priority === null) {
            $calc = new PriorityCalculator();
            $priority = $calc->infer($source, $type);
        }
        $priority = max(0, min(100, (int) $priority));

        $tags = null;
        if (isset($e['tags'])) {
            $tags = is_array($e['tags']) ? wp_json_encode($e['tags']) : (string) $e['tags'];
        }
        if ($tags === null) {
            $fallback = array_values(array_unique(array_filter([$source, $type])));
            $tags = $fallback ? wp_json_encode($fallback) : null;
        }

        $context = null;
        if (isset($e['context'])) {
            $context = is_array($e['context']) ? wp_json_encode($e['context']) : (string) $e['context'];
        }

        $status = isset($e['status']) ? (int) $e['status'] : 0;

        $wpdb->insert(
            $this->table(),
            [
                'source'     => $source,
                'type'       => $type,
                'title'      => $title,
                'message'    => $message,
                'status'     => $status,
                'priority'   => $priority,
                'context'    => $context,
                'tags'       => $tags,
                'created_at' => $now,
                'updated_at' => null,
                'read_at'    => null,
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        $inserted_id = (int) $wpdb->insert_id;
        if ($inserted_id > 0) {
            $this->dispatchChannels(
                [
                    'source'  => $source,
                    'type'    => $type,
                    'title'   => $title,
                    'message' => $message,
                    'context' => $context,
                ],
                $e
            );
        }

        return $inserted_id;
    }

    /**
     * Send newly created notification to configured channels.
     *
     * @param array<string,mixed> $notification
     * @param array<string,mixed> $rawInput
     */
    private function dispatchChannels(array $notification, array $rawInput): void {
        if (!empty($rawInput['no_dispatch'])) {
            return;
        }

        $source = sanitize_key((string) ($notification['source'] ?? ''));
        $type = sanitize_key((string) ($notification['type'] ?? ''));

        // Avoid loop: sending email triggers wp_mail_succeeded.
        if ($source === 'wordpress' && $type === 'email_sent') {
            return;
        }

        // Custom hook test flow already sends selected channels explicitly.
        if ($source === 'custom_hook') {
            return;
        }

        $settings = new SettingsRepository();
        $general = $settings->getGeneral();
        $pro = $settings->getPro();

        $context = [];
        $rawContext = $notification['context'] ?? null;
        if (is_array($rawContext)) {
            $context = $rawContext;
        } elseif (is_string($rawContext) && $rawContext !== '') {
            $decoded = json_decode($rawContext, true);
            if (is_array($decoded)) {
                $context = $decoded;
            }
        }

        $payload = [
            'title'   => (string) ($notification['title'] ?? ''),
            'subject' => (string) ($notification['title'] ?? ''),
            'body'    => wp_strip_all_tags((string) ($notification['message'] ?? '')),
            'message' => wp_strip_all_tags((string) ($notification['message'] ?? '')),
            'source'  => (string) ($notification['source'] ?? ''),
            'type'    => (string) ($notification['type'] ?? ''),
            'context' => $context,
        ];

        $channels = ['email'];

        $proActive = defined('NH_PRO_ACTIVE') && NH_PRO_ACTIVE;
        $hasLicenseFacade = class_exists('NH_License');
        $isProLicensed = !$hasLicenseFacade
            || !method_exists('NH_License', 'is_pro')
            || \NH_License::is_pro();

        if ($proActive && !empty($pro['telegram_bot_token']) && !empty($pro['telegram_chat_id'])) {
            if (
                !$hasLicenseFacade
                || !method_exists('NH_License', 'can')
                || \NH_License::can('telegram')
                || $isProLicensed
            ) {
                $channels[] = 'telegram';
            }
        }

        if ($proActive && !empty($pro['slack_webhook'])) {
            if (
                !$hasLicenseFacade
                || !method_exists('NH_License', 'can')
                || \NH_License::can('slack')
                || $isProLicensed
            ) {
                $channels[] = 'slack';
            }
        }

        if (!empty($general['email_to']) && is_email((string) $general['email_to'])) {
            $payload['to'] = (string) $general['email_to'];
        }

        $dispatcher = ServiceFactory::makeNotificationDispatcher();
        foreach (array_values(array_unique($channels)) as $channel) {
            $dispatcher->queueSend((string) $channel, $payload);
        }
    }

    /**
     * Get notification by ID.
     *
     * @return array<string,mixed>|null
     */
    public function getById(int $id): ?array {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return null;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $this->table() . ' WHERE id=%d', $id), ARRAY_A);

        return $row ?: null;
    }

    public function markRead(int $id): bool {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update($this->table(), ['read_at' => current_time('mysql')], ['id' => $id], ['%s'], ['%d']);

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function markUnread(int $id): bool {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update($this->table(), ['read_at' => null], ['id' => $id], ['%s'], ['%d']);

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function markImportant(int $id): bool {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return false;
        }

        // status=3 (important)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update($this->table(), ['status' => 3], ['id' => $id], ['%d'], ['%d']);

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function unmarkImportant(int $id): bool {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $updated = $wpdb->update($this->table(), ['status' => 0], ['id' => $id, 'status' => 3], ['%d'], ['%d', '%d']);

        return ($updated !== false) && empty($wpdb->last_error);
    }

    public function deleteById(int $id): bool {
        global $wpdb;

        $id = absint($id);
        if (!$id) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $deleted = $wpdb->delete($this->table(), ['id' => $id], ['%d']);

        return ($deleted !== false) && empty($wpdb->last_error);
    }

    public function countUnread(): int {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' WHERE ' . $this->hiddenTypesSql() . ' AND status IN (0,3) AND read_at IS NULL';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Dashboard-style query (ported from NH_Table_Query::get_notifications).
     *
     * @param array<string,mixed> $args
     * @return array{items: array<int, array<string,mixed>>, total: int}
     */
    public function queryForDashboard(array $args = []): array {
        global $wpdb;
        $table = $this->table();

        $defaults = [
            'status_filter'      => 'all',
            'search'             => '',
            'filter_source'      => '',
            'filter_type'        => '',
            'filter_priority'    => '',
            'filter_created'     => '',
            'filter_read_status' => '',
            'orderby'            => 'created_at',
            'order'              => 'DESC',
            'per_page'           => 20,
            'paged'              => 1,
        ];
        $args = wp_parse_args($args, $defaults);

        $where  = 'WHERE 1=1 AND ' . $this->hiddenTypesSql();
        $params = [];

        if ($args['status_filter'] === 'unread') {
            $where .= ' AND read_at IS NULL AND status IN (0,3)';
        } elseif ($args['status_filter'] === 'archived') {
            $where .= ' AND status = 1';
        } elseif ($args['status_filter'] === 'important') {
            $where .= ' AND status = 3';
        }

        if ($args['search'] !== '') {
            $where .= ' AND (source LIKE %s OR title LIKE %s OR message LIKE %s)';
            $like = '%' . $wpdb->esc_like((string) $args['search']) . '%';
            array_push($params, $like, $like, $like);
        }

        if ($args['filter_source'] !== '') {
            $where    .= ' AND source = %s';
            $params[] = (string) $args['filter_source'];
        }

        if ($args['filter_type'] !== '') {
            $where    .= ' AND type = %s';
            $params[] = (string) $args['filter_type'];
        }

        if ($args['filter_priority'] !== '') {
            $where    .= ' AND CAST(priority AS SIGNED) = %d';
            $params[] = (int) $args['filter_priority'];
        }

        if ($args['filter_created'] !== '') {
            switch ((string) $args['filter_created']) {
                case 'today':
                    $where .= ' AND DATE(created_at) = CURDATE()';
                    break;
                case 'yesterday':
                    $where .= ' AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
                    break;
                case 'last_7_days':
                    $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'last_30_days':
                    $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'last_year':
                    $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
            }
        }

        if ($args['filter_read_status'] !== '') {
            if ($args['filter_read_status'] === 'read') {
                $where .= ' AND read_at IS NOT NULL';
            } elseif ($args['filter_read_status'] === 'unread') {
                $where .= ' AND read_at IS NULL';
            } elseif ($args['filter_read_status'] === 'important') {
                $where .= ' AND status = 3';
            }
        }

        $count_sql = "SELECT COUNT(*) FROM {$table} {$where}";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = empty($params) ? (int) $wpdb->get_var($count_sql)
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            : (int) $wpdb->get_var($wpdb->prepare($count_sql, $params));

        $per_page = max(1, (int) $args['per_page']);
        $paged    = max(1, (int) $args['paged']);
        $offset   = ($paged - 1) * $per_page;

        $allowed_orderby = [
            'id'         => 'id',
            'title'      => 'title',
            'created_at' => 'created_at',
            'source'     => 'source',
            'priority'   => 'priority',
            'type'       => 'type',
            'status'     => 'status',
            'read_at'    => 'read_at',
        ];
        $orderby_key = sanitize_key((string) $args['orderby']);
        $orderby     = $allowed_orderby[$orderby_key] ?? 'created_at';
        $order       = (strtoupper((string) $args['order']) === 'ASC') ? 'ASC' : 'DESC';

        $query_sql = "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $query_params = $params;
        array_push($query_params, $per_page, $offset);

        $items = empty($query_params)
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            ? $wpdb->get_results($query_sql, ARRAY_A)
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            : $wpdb->get_results($wpdb->prepare($query_sql, $query_params), ARRAY_A);

        return [
            'items' => is_array($items) ? $items : [],
            'total' => $total,
        ];
    }
}
