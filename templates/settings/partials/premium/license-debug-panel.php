<?php
/**
 * Premium License Debug Panel
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="nh-license-debug-panel">
    <h2><?php esc_html_e('License Debug', 'notification-hub'); ?></h2>

    <table class="widefat striped">
        <tbody>
            <tr>
                <td><strong><?php esc_html_e('Pro addon active', 'notification-hub'); ?></strong></td>
                <td><?php echo esc_html(class_exists('NH_License') && method_exists('NH_License', 'is_pro_addon_active') && NH_License::is_pro_addon_active() ? 'yes' : 'no'); ?></td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('License key', 'notification-hub'); ?></strong></td>
                <td><?php echo esc_html(class_exists('NH_License') && method_exists('NH_License', 'get_key') ? (string) NH_License::get_key() : ''); ?></td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('Server URL', 'notification-hub'); ?></strong></td>
                <td><?php echo esc_html(class_exists('NH_License') && defined('NH_License::OPT_SERVER_URL') ? (string) get_option(NH_License::OPT_SERVER_URL, '') : ''); ?></td>
            </tr>
        </tbody>
    </table>
</div>
