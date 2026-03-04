<?php
/**
 * NH_Table_Filters
 *
 * Table filters + export UI for the notifications dashboard.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Table_Filters {
    /**
     * @var array<int,string>
     */
    private const HIDDEN_TYPES = ['dispatch_check', 'email_sent'];

    private static function hidden_types_sql(): string {
        $types = array_map('sanitize_key', self::HIDDEN_TYPES);
        $quoted = array_map(static function (string $type): string {
            return "'" . esc_sql($type) . "'";
        }, $types);

        return 'type NOT IN (' . implode(', ', $quoted) . ')';
    }

    /**
     * Render filter controls + CSV export button.
     *
     * @since 1.6.2
     * @return void
     */
    public static function render(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';
        $hidden = self::hidden_types_sql();

        $filter_source      = isset($_GET['filter_source']) ? sanitize_text_field(wp_unslash($_GET['filter_source'])) : '';
        $filter_type        = isset($_GET['filter_type']) ? sanitize_text_field(wp_unslash($_GET['filter_type'])) : '';
        $filter_priority    = isset($_GET['filter_priority']) ? sanitize_text_field(wp_unslash($_GET['filter_priority'])) : '';
        $filter_created     = isset($_GET['filter_created']) ? sanitize_key(wp_unslash($_GET['filter_created'])) : '';
        $filter_read_status = isset($_GET['filter_read_status']) ? sanitize_key(wp_unslash($_GET['filter_read_status'])) : '';

        $allowed_created = ['', 'today', 'yesterday', 'last_7_days', 'last_30_days', 'last_year'];
        if (!in_array($filter_created, $allowed_created, true)) {
            $filter_created = '';
        }

        $allowed_read_status = ['', 'read', 'unread', 'important'];
        if (!in_array($filter_read_status, $allowed_read_status, true)) {
            $filter_read_status = '';
        }

        if ($filter_priority !== '') {
            $filter_priority = (string) absint($filter_priority);
        }

        $has_filters = ($filter_source || $filter_type || $filter_priority || $filter_created || $filter_read_status);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $sources = $wpdb->get_col("SELECT DISTINCT source FROM {$table} WHERE {$hidden} AND source IS NOT NULL AND source != '' ORDER BY source ASC");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $types = $wpdb->get_col("SELECT DISTINCT type FROM {$table} WHERE {$hidden} AND type IS NOT NULL AND type != '' ORDER BY type ASC");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $priorities = $wpdb->get_col("SELECT DISTINCT priority FROM {$table} WHERE {$hidden} AND priority IS NOT NULL ORDER BY priority ASC");

        ?>
        <div class="alignleft actions nh-filters">
            <?php self::render_time_filter($filter_created); ?>
            <?php self::render_source_filter(is_array($sources) ? $sources : [], $filter_source); ?>
            <?php self::render_type_filter(is_array($types) ? $types : [], $filter_type); ?>
            <?php self::render_priority_filter(is_array($priorities) ? $priorities : [], $filter_priority); ?>
            <?php self::render_status_filter($filter_read_status); ?>
            <?php self::render_filter_button($has_filters); ?>
        </div>
        <?php
    }

    private static function render_time_filter(string $selected): void {
        ?>
        <select name="filter_created" id="nh-filter-created">
            <option value=""><?php esc_html_e('All time', 'notification-hub'); ?></option>
            <option value="today" <?php selected($selected, 'today'); ?>><?php esc_html_e('Today', 'notification-hub'); ?></option>
            <option value="yesterday" <?php selected($selected, 'yesterday'); ?>><?php esc_html_e('Yesterday', 'notification-hub'); ?></option>
            <option value="last_7_days" <?php selected($selected, 'last_7_days'); ?>><?php esc_html_e('Last 7 days', 'notification-hub'); ?></option>
            <option value="last_30_days" <?php selected($selected, 'last_30_days'); ?>><?php esc_html_e('Last 30 days', 'notification-hub'); ?></option>
            <option value="last_year" <?php selected($selected, 'last_year'); ?>><?php esc_html_e('Last year', 'notification-hub'); ?></option>
        </select>
        <?php
    }

    private static function render_source_filter(array $sources, string $selected): void {
        ?>
        <select name="filter_source" id="nh-filter-source">
            <option value=""><?php esc_html_e('All sources', 'notification-hub'); ?></option>
            <?php foreach ($sources as $source): ?>
                <option value="<?php echo esc_attr($source); ?>" <?php selected($selected, (string) $source); ?>>
                    <?php echo esc_html($source); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private static function render_type_filter(array $types, string $selected): void {
        ?>
        <select name="filter_type" id="nh-filter-type">
            <option value=""><?php esc_html_e('All types', 'notification-hub'); ?></option>
            <?php foreach ($types as $type): ?>
                <option value="<?php echo esc_attr($type); ?>" <?php selected($selected, (string) $type); ?>>
                    <?php echo esc_html($type); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private static function render_priority_filter(array $priorities, string $selected): void {
        ?>
        <select name="filter_priority" id="nh-filter-priority">
            <option value=""><?php esc_html_e('All priorities', 'notification-hub'); ?></option>
            <?php foreach ($priorities as $priority): ?>
                <option value="<?php echo esc_attr($priority); ?>" <?php selected($selected, (string) $priority); ?>>
                    <?php echo esc_html($priority); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private static function render_status_filter(string $selected): void {
        ?>
        <select name="filter_read_status" id="nh-filter-read-status">
            <option value=""><?php esc_html_e('All statuses', 'notification-hub'); ?></option>
            <option value="read" <?php selected($selected, 'read'); ?>><?php esc_html_e('Read', 'notification-hub'); ?></option>
            <option value="unread" <?php selected($selected, 'unread'); ?>><?php esc_html_e('Unread', 'notification-hub'); ?></option>
            <option value="important" <?php selected($selected, 'important'); ?>><?php esc_html_e('Important', 'notification-hub'); ?></option>
        </select>
        <?php
    }

    private static function render_filter_button(bool $has_filters): void {
        if (!$has_filters) {
            ?>
            <button type="button" class="button" id="nh-apply-filters">
                <?php esc_html_e('Filter', 'notification-hub'); ?>
            </button>
            <?php
            return;
        }

        $clear_url = remove_query_arg(['filter_source', 'filter_type', 'filter_priority', 'filter_created', 'filter_read_status']);
        ?>
        <a href="<?php echo esc_url($clear_url); ?>" class="button">
            <?php esc_html_e('Clear filters', 'notification-hub'); ?>
        </a>
        <?php
    }

}
