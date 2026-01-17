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
     * Render filter controls + CSV export button.
     *
     * @since 1.6.2
     * @return void
     */
    public static function render(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $filter_source      = isset($_GET['filter_source']) ? sanitize_text_field(wp_unslash($_GET['filter_source'])) : '';
        $filter_type        = isset($_GET['filter_type']) ? sanitize_text_field(wp_unslash($_GET['filter_type'])) : '';
        $filter_priority    = isset($_GET['filter_priority']) ? sanitize_text_field(wp_unslash($_GET['filter_priority'])) : '';
        $filter_created     = isset($_GET['filter_created']) ? sanitize_key(wp_unslash($_GET['filter_created'])) : '';
        $filter_read_status = isset($_GET['filter_read_status']) ? sanitize_key(wp_unslash($_GET['filter_read_status'])) : '';

        $has_filters = ($filter_source || $filter_type || $filter_priority || $filter_created || $filter_read_status);

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sources = $wpdb->get_col("SELECT DISTINCT source FROM {$table} WHERE source IS NOT NULL AND source != '' ORDER BY source ASC");
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $types = $wpdb->get_col("SELECT DISTINCT type FROM {$table} WHERE type IS NOT NULL AND type != '' ORDER BY type ASC");
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $priorities = $wpdb->get_col("SELECT DISTINCT priority FROM {$table} WHERE priority IS NOT NULL ORDER BY priority ASC");

        ?>
        <div class="alignleft actions nh-filters">
            <?php self::render_time_filter($filter_created); ?>
            <?php self::render_source_filter(is_array($sources) ? $sources : [], $filter_source); ?>
            <?php self::render_type_filter(is_array($types) ? $types : [], $filter_type); ?>
            <?php self::render_priority_filter(is_array($priorities) ? $priorities : [], $filter_priority); ?>
            <?php self::render_status_filter($filter_read_status); ?>
            <?php self::render_filter_button($has_filters); ?>
        </div>

        <div class="alignleft actions nh-export">
            <?php self::render_export_button(); ?>
        </div>
        <?php
    }

    /**
     * Time filter.
     *
     * @since 1.6.2
     * @param string $selected Selected key.
     * @return void
     */
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

    /**
     * Source filter.
     *
     * @since 1.6.2
     * @param array  $sources Source list.
     * @param string $selected Selected source.
     * @return void
     */
    private static function render_source_filter(array $sources, string $selected): void {
        ?>
        <select name="filter_source" id="nh-filter-source">
            <option value=""><?php esc_html_e('All sources', 'notification-hub'); ?></option>
            <?php foreach ($sources as $source): ?>
                <option value="<?php echo esc_attr($source); ?>" <?php selected($selected, $source); ?>>
                    <?php echo esc_html($source); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Type filter.
     *
     * @since 1.6.2
     * @param array  $types Type list.
     * @param string $selected Selected type.
     * @return void
     */
    private static function render_type_filter(array $types, string $selected): void {
        ?>
        <select name="filter_type" id="nh-filter-type">
            <option value=""><?php esc_html_e('All types', 'notification-hub'); ?></option>
            <?php foreach ($types as $type): ?>
                <option value="<?php echo esc_attr($type); ?>" <?php selected($selected, $type); ?>>
                    <?php echo esc_html($type); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Priority filter.
     *
     * @since 1.6.2
     * @param array  $priorities Priority list.
     * @param string $selected Selected priority.
     * @return void
     */
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

    /**
     * Read status filter.
     *
     * @since 1.6.2
     * @param string $selected Selected key.
     * @return void
     */
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

    /**
     * Filter / Clear button.
     *
     * @since 1.6.2
     * @param bool $has_filters Whether filters exist.
     * @return void
     */
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

    /**
     * Export CSV button.
     *
     * @since 1.6.2
     * @return void
     */
    private static function render_export_button(): void {
        $export_url = wp_nonce_url(admin_url('admin-post.php?action=nh_export_csv'), 'nh_export_csv');
        ?>
        <a class="button button-secondary nh-export-csv" href="<?php echo esc_url($export_url); ?>">
            <span class="dashicons dashicons-download nh-export-csv__icon" aria-hidden="true"></span>
            <?php esc_html_e('Export CSV', 'notification-hub'); ?>
        </a>
        <?php
    }
}
