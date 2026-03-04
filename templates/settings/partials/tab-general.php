<?php
/**
 * Settings Tab: General
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$retention_days = absint((string) get_option('nh_retention_days', 90));
if ($retention_days <= 0) {
    $retention_days = 90;
}

$email_to = (string) get_option('nh_email_to', get_option('admin_email'));
if ($email_to === '') {
    $email_to = (string) get_option('admin_email');
}

$keep_data_raw = get_option('nh_keep_data_on_uninstall', '1');
$keep_data_on_uninstall = !in_array((string) $keep_data_raw, ['0', 'false'], true);
?>

<div
    id="nh-tab-general"
    class="nh-tab <?php echo $active_tab === 'general' ? 'is-active' : ''; ?>"
    data-tab="general"
>
    <table class="form-table">
        <tr>
            <th>
                <label for="nh_retention_days"><?php esc_html_e('Retention (days)', 'notification-hub'); ?></label>
            </th>
            <td>
                <input
                    name="nh_retention_days"
                    id="nh_retention_days"
                    type="number"
                    min="1"
                    value="<?php echo esc_attr($retention_days); ?>"
                >
            </td>
        </tr>

        <tr>
            <th>
                <label for="nh_email_to"><?php esc_html_e('Email To', 'notification-hub'); ?></label>
            </th>
            <td>
                <input
                    name="nh_email_to"
                    id="nh_email_to"
                    type="email"
                    value="<?php echo esc_attr($email_to); ?>"
                >

                <p>
                    <a
                        href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=email&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=general')); ?>"
                        data-tab="general"
                        class="button nh-test-btn"
                    >
                        <?php esc_html_e('Send Test Email', 'notification-hub'); ?>
                    </a>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php esc_html_e('Keep data on uninstall', 'notification-hub'); ?></th>
            <td>
                <label>
                    <input type="hidden" name="nh_keep_data_on_uninstall" value="0">
                    <input
                        type="checkbox"
                        name="nh_keep_data_on_uninstall"
                        value="1"
                        <?php checked($keep_data_on_uninstall); ?>
                    />
                    <?php esc_html_e('Do not delete plugin tables on uninstall', 'notification-hub'); ?>
                </label>
            </td>
        </tr>
    </table>
</div>
