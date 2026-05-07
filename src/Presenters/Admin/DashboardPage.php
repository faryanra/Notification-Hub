<?php
/**
 * NH_Dashboard
 *
 * Dashboard controller that renders the notifications list table.
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class NH_Dashboard {

    /**
     * Registry container.
     *
     * @since 1.0.0
     * @var mixed
     */
    protected $registry;

    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
     * Render dashboard page.
     *
     * @since 1.0.0
     * @return void
     */
    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'notification-hub'));
        }

        $status_filter = isset($_GET['filter_status'])
            ? sanitize_key(wp_unslash($_GET['filter_status']))
            : 'all';

        $allowed_status = ['all', 'unread', 'read', 'archived'];
        if (!in_array($status_filter, $allowed_status, true)) {
            $status_filter = 'all';
        }

        $prev_seen = get_user_meta(get_current_user_id(), 'nh_last_seen_at', true);
        $prev_seen = $prev_seen ? (string) $prev_seen : '1970-01-01 00:00:00';

        $this->track_last_seen($prev_seen);

        $table = new NH_Notifications_Table($status_filter, $prev_seen);
        $table->prepare_items();

        $this->render_header();
        $this->render_views($table->get_views());
        $this->render_table($table);
        $this->render_footer();
    }

    /**
     * Track user's last visit timestamp + localize previous timestamp.
     *
     * @since 1.0.0
     * @param string $prev_seen Previous seen value.
     * @return void
     */
    private function track_last_seen(string $prev_seen): void {
        if (wp_script_is('nh-dashboard', 'enqueued') || wp_script_is('nh-dashboard', 'done')) {
            wp_localize_script('nh-dashboard', 'nhSeen', [
                'prev' => $prev_seen,
            ]);
        }

        update_user_meta(get_current_user_id(), 'nh_last_seen_at', current_time('mysql'));
    }

    /**
     * Render page header.
     *
     * @since 1.0.0
     * @return void
     */
    private function render_header(): void {
        echo '<div class="wrap">';
        echo '<div id="nh-table-loader" class="nh-table-loader" aria-hidden="true">';
        echo '<span class="spinner is-active nh-table-loader__spinner"></span>';
        echo '</div>';
        echo '<h1 class="wp-heading-inline">' . esc_html__('Notifications Dashboard', 'notification-hub') . '</h1>';
        echo '<hr class="wp-header-end">';
    }

    /**
     * Render filter views (tabs).
     *
     * @since 1.0.0
     * @param array $views Views markup.
     * @return void
     */
    private function render_views($views): void {
        if (empty($views) || !is_array($views)) {
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<ul class="subsubsub">';
        $last = array_key_last($views);

        foreach ($views as $key => $view) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<li>' . $view . ($key !== $last ? ' | ' : '') . '</li>';
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</ul>';
    }

    /**
     * Render table.
     *
     * @since 1.0.0
     * @param NH_Notifications_Table $table Table instance.
     * @return void
     */
    private function render_table($table): void {
        echo '<form method="post">';
        wp_nonce_field('bulk-notifications');
        echo '<input type="hidden" name="page" value="nh-dashboard" />';

        $export_url = wp_nonce_url(admin_url('admin-post.php?action=nh_export_csv'), 'nh_export_csv');
        echo '<div class="nh-table-toolbar">';
        $table->search_box(esc_html__('Search Notifications', 'notification-hub'), 'nh-search');
        echo '<a class="button button-secondary nh-export-csv" href="' . esc_url($export_url) . '">';
        echo '<span class="dashicons dashicons-download nh-export-csv__icon" aria-hidden="true"></span> ';
        echo esc_html__('Export CSV', 'notification-hub');
        echo '</a>';
        echo '</div>';
        $table->display();

        echo '</form>';
    }

    /**
     * Render page footer.
     *
     * @since 1.0.0
     * @return void
     */
    private function render_footer(): void {
        echo '</div>';
    }
}

