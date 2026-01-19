<?php
/**
 * Premium License Box
 *
 * Premium-only UI partial (kept in the same folder as other templates,
 * but clearly marked for extraction by its filename prefix).
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

$state = class_exists('NH_License') ? NH_License::get_state() : [];
$status = isset($state['status']) ? (string) $state['status'] : 'unknown';
$message = isset($state['message']) ? (string) $state['message'] : '';

$key = class_exists('NH_License') ? NH_License::get_key() : '';
$server_url = class_exists('NH_License') ? NH_License::get_server_url() : '';
?>

<div class="nh-license" id="nh-license-box">
    <h2><?php esc_html_e('License', 'notification-hub'); ?></h2>

    <p>
        <strong><?php esc_html_e('Status:', 'notification-hub'); ?></strong>
        <span class="nh-license-status nh-license-status--<?php echo esc_attr($status); ?>">
            <?php echo esc_html(ucfirst($status)); ?>
        </span>
    </p>

    <?php if ($message !== '') : ?>
        <p class="description"><?php echo esc_html($message); ?></p>
    <?php endif; ?>

    <table class="form-table">
        <tr>
            <th><label for="nh_license_server_url"><?php esc_html_e('License Server URL', 'notification-hub'); ?></label></th>
            <td>
                <input
                    type="url"
                    id="nh_license_server_url"
                    name="nh_license_server_url"
                    value="<?php echo esc_attr($server_url); ?>"
                    class="regular-text"
                    data-lockable="1"
                />
            </td>
        </tr>

        <tr>
            <th><label for="nh_license_key"><?php esc_html_e('License Key', 'notification-hub'); ?></label></th>
            <td>
                <input
                    type="text"
                    id="nh_license_key"
                    name="nh_license_key"
                    value="<?php echo esc_attr($key); ?>"
                    class="regular-text"
                    data-lockable="1"
                />

                <p>
                    <button
                        class="button"
                        id="nh-license-edit"
                        type="button"
                        data-label-edit="<?php echo esc_attr__('Edit', 'notification-hub'); ?>"
                        data-label-cancel="<?php echo esc_attr__('Cancel', 'notification-hub'); ?>"
                        aria-pressed="false"
                    >
                        <?php esc_html_e('Edit', 'notification-hub'); ?>
                    </button>
                </p>
            </td>
        </tr>
    </table>
</div>
