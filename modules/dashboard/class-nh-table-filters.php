<?php
/**
 * Table Filters & Export UI
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Table_Filters {

    /**
     * Render filter controls + CSV export button
     */
    public static function render() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_notifications';

        $filter_source = $_GET['filter_source'] ?? '';
        $filter_type = $_GET['filter_type'] ?? '';
        $filter_priority = $_GET['filter_priority'] ?? '';
        $filter_created = $_GET['filter_created'] ?? '';
        $filter_read_status = $_GET['filter_read_status'] ?? '';
        
        $has_filters = $filter_source || $filter_type || $filter_priority || $filter_created || $filter_read_status;

        $sources = $wpdb->get_col("SELECT DISTINCT source FROM {$table} WHERE source IS NOT NULL AND source != '' ORDER BY source ASC");
        $types = $wpdb->get_col("SELECT DISTINCT type FROM {$table} WHERE type IS NOT NULL AND type != '' ORDER BY type ASC");
        $priorities = $wpdb->get_col("SELECT DISTINCT priority FROM {$table} WHERE priority IS NOT NULL ORDER BY priority ASC");

        ?>
        <div class="alignleft actions">
            <?php self::render_time_filter($filter_created); ?>
            <?php self::render_source_filter($sources, $filter_source); ?>
            <?php self::render_type_filter($types, $filter_type); ?>
            <?php self::render_priority_filter($priorities, $filter_priority); ?>
            <?php self::render_status_filter($filter_read_status); ?>
            <?php self::render_filter_button($has_filters); ?>
        </div>

        <div class="alignleft actions" style="margin-left:10px;">
            <?php self::render_export_button(); ?>
        </div>

        <?php self::render_filter_script(); ?>
        <?php
    }

    private static function render_time_filter($selected) {
        ?>
        <select name="filter_created" id="nh-filter-created">
            <option value=""><?php esc_html_e('All Time', 'notification-hub'); ?></option>
            <option value="today" <?php selected($selected, 'today'); ?>><?php esc_html_e('Today', 'notification-hub'); ?></option>
            <option value="yesterday" <?php selected($selected, 'yesterday'); ?>><?php esc_html_e('Yesterday', 'notification-hub'); ?></option>
            <option value="last_7_days" <?php selected($selected, 'last_7_days'); ?>><?php esc_html_e('Last 7 Days', 'notification-hub'); ?></option>
            <option value="last_30_days" <?php selected($selected, 'last_30_days'); ?>><?php esc_html_e('Last 30 Days', 'notification-hub'); ?></option>
            <option value="last_year" <?php selected($selected, 'last_year'); ?>><?php esc_html_e('Last Year', 'notification-hub'); ?></option>
        </select>
        <?php
    }

    private static function render_source_filter($sources, $selected) {
        ?>
        <select name="filter_source" id="nh-filter-source">
            <option value=""><?php esc_html_e('All Sources', 'notification-hub'); ?></option>
            <?php foreach ($sources as $source): ?>
                <option value="<?php echo esc_attr($source); ?>" <?php selected($selected, $source); ?>>
                    <?php echo esc_html($source); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private static function render_type_filter($types, $selected) {
        ?>
        <select name="filter_type" id="nh-filter-type">
            <option value=""><?php esc_html_e('All Types', 'notification-hub'); ?></option>
            <?php foreach ($types as $type): ?>
                <option value="<?php echo esc_attr($type); ?>" <?php selected($selected, $type); ?>>
                    <?php echo esc_html($type); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private static function render_priority_filter($priorities, $selected) {
        ?>
        <select name="filter_priority" id="nh-filter-priority">
            <option value=""><?php esc_html_e('All Priorities', 'notification-hub'); ?></option>
            <?php foreach ($priorities as $priority): ?>
                <option value="<?php echo esc_attr($priority); ?>" <?php selected($selected, $priority); ?>>
                    <?php echo esc_html($priority); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private static function render_status_filter($selected) {
        ?>
        <select name="filter_read_status" id="nh-filter-read-status">
            <option value=""><?php esc_html_e('All Statuses', 'notification-hub'); ?></option>
            <option value="read" <?php selected($selected, 'read'); ?>><?php esc_html_e('Read', 'notification-hub'); ?></option>
            <option value="unread" <?php selected($selected, 'unread'); ?>><?php esc_html_e('Unread', 'notification-hub'); ?></option>
            <option value="important" <?php selected($selected, 'important'); ?>><?php esc_html_e('Important', 'notification-hub'); ?></option>
        </select>
        <?php
    }

    private static function render_filter_button($has_filters) {
        if (!$has_filters): ?>
            <button type="button" class="button" id="nh-apply-filters">
                <?php esc_html_e('Filter', 'notification-hub'); ?>
            </button>
        <?php else: 
            $clear_url = remove_query_arg(['filter_source', 'filter_type', 'filter_priority', 'filter_created', 'filter_read_status']);
            ?>
            <a href="<?php echo esc_url($clear_url); ?>" class="button">
                <?php esc_html_e('Clear Filters', 'notification-hub'); ?>
            </a>
        <?php endif;
    }

    private static function render_export_button() {
        $export_url = wp_nonce_url(admin_url('admin-post.php?action=nh_export_csv'), 'nh_export_csv');
        ?>
        <a class="button button-secondary" href="<?php echo esc_url($export_url); ?>">
            <span class="dashicons dashicons-download" style="vertical-align:middle;"></span>
            <?php esc_html_e('Export CSV', 'notification-hub'); ?>
        </a>
        <?php
    }

    private static function render_filter_script() {
        ?>
        <script>
        (function() {
            const btn = document.getElementById('nh-apply-filters');
            if (!btn) return;

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const params = new URLSearchParams(window.location.search);
                
                const filters = {
                    filter_created: document.getElementById('nh-filter-created').value,
                    filter_source: document.getElementById('nh-filter-source').value,
                    filter_type: document.getElementById('nh-filter-type').value,
                    filter_priority: document.getElementById('nh-filter-priority').value,
                    filter_read_status: document.getElementById('nh-filter-read-status').value
                };

                Object.entries(filters).forEach(([key, val]) => {
                    val ? params.set(key, val) : params.delete(key);
                });

                window.location.href = '?' + params.toString();
            });
        })();
        </script>
        <?php
    }
}
