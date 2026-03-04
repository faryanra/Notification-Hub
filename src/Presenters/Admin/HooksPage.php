<?php

namespace NotificationHub\Presenters\Admin;

/**
 * Hooks page presenter.
 *
 * @since 1.7.2
 */
final class HooksPage {
    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'notification-hub'));
        }

        $file = NH_PLUGIN_DIR . 'templates/admin/hooks.php';
        if (file_exists($file)) {
            include $file;
            return;
        }

        echo '<div class="wrap"><h1>' . esc_html__('Hooks', 'notification-hub') . '</h1><p>' . esc_html__('Template not found.', 'notification-hub') . '</p></div>';
    }
}
