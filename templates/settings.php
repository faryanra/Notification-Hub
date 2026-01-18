<?php
/**
 * Settings Template
 *
 * Renders Notification Hub settings page (General / Pro tabs).
 * License box is extracted into a partial for maintainability.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Read runtime flags.
 */
$can_telegram = class_exists('NH_License') ? NH_License::can('telegram') : false;
$can_slack    = class_exists('NH_License') ? NH_License::can('slack') : false;
$active_tab   = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';

/**
 * Render admin notices from query params.
 */
$channel = isset($_GET['nh_test']) ? sanitize_text_field(wp_unslash($_GET['nh_test'])) : '';
$success = isset($_GET['success']) ? sanitize_text_field(wp_unslash($_GET['success'])) : '';
?>

<div class="wrap">
    <h1><?php esc_html_e('Notification Hub - Settings', 'notification-hub'); ?></h1>

    <?php if ($success !== '') : ?>
        <?php if ($success === '1') : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    echo sprintf(
                        /* translators: %s: channel name (email/telegram/slack) */
                        esc_html__('Test sent successfully to %s.', 'notification-hub'),
                        '<strong>' . esc_html($channel !== '' ? $channel : 'email') . '</strong>'
                    );
                    ?>
                </p>
            </div>
        <?php else : ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    echo sprintf(
                        /* translators: %s: channel name (email/telegram/slack) */
                        esc_html__('Test failed to send to %s.', 'notification-hub'),
                        '<strong>' . esc_html($channel !== '' ? $channel : 'email') . '</strong>'
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['settings-updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully.', 'notification-hub'); ?></p>
        </div>
    <?php endif; ?>

    <h2 class="nav-tab-wrapper nh-settings-tabs" data-active-tab="<?php echo esc_attr($active_tab); ?>">
        <a
            href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=general')); ?>"
            class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"
            data-tab="general"
        >
            <?php esc_html_e('General', 'notification-hub'); ?>
        </a>

        <a
            href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=pro')); ?>"
            class="nav-tab <?php echo $active_tab === 'pro' ? 'nav-tab-active' : ''; ?>"
            data-tab="pro"
        >
            <?php esc_html_e('Pro Channels', 'notification-hub'); ?>
        </a>
    </h2>

    <?php
    $license_partial = NH_PLUGIN_DIR . 'templates/partials/license-box.php';
    if (file_exists($license_partial)) {
        include $license_partial;
    }
    ?>

    <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
        <?php settings_fields('nh_settings'); ?>
        <?php do_settings_sections('nh_settings'); ?>

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
                            value="<?php echo esc_attr(get_option('nh_retention_days', 90)); ?>"
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
                            value="<?php echo esc_attr(get_option('nh_email_to', get_option('admin_email'))); ?>"
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
                                <?php checked(get_option('nh_keep_data_on_uninstall', true)); ?>
                            />
                            <?php esc_html_e('Do not delete plugin tables on uninstall', 'notification-hub'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <div
            id="nh-tab-pro"
            class="nh-tab <?php echo $active_tab === 'pro' ? 'is-active' : ''; ?>"
            data-tab="pro"
        >
            <table class="form-table">
                <tr>
                    <th><label for="nh_telegram_bot_token"><?php esc_html_e('Telegram Bot Token', 'notification-hub'); ?></label></th>
                    <td>
                        <input
                            name="nh_telegram_bot_token"
                            id="nh_telegram_bot_token"
                            type="text"
                            value="<?php echo esc_attr(get_option('nh_telegram_bot_token', '')); ?>"
                            class="regular-text"
                            <?php echo $can_telegram ? '' : 'disabled'; ?>
                        >

                        <p class="description">
                            <?php
                            echo sprintf(
                                /* translators: %s: example token */
                                esc_html__('Enter your BotFather token. Example: %s', 'notification-hub'),
                                '<code>123456:ABC-xyz</code>'
                            );
                            ?>
                        </p>

                        <?php if (!$can_telegram) : ?>
                            <p class="description">
                                <em><?php esc_html_e('This field is disabled because it’s only available in the Pro version.', 'notification-hub'); ?></em>
                            </p>
                        <?php else : ?>
                            <p>
                                <a
                                    href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=telegram&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=pro')); ?>"
                                    data-tab="pro"
                                    class="button nh-test-btn"
                                >
                                    <?php esc_html_e('Send Test to Telegram', 'notification-hub'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th><label for="nh_telegram_chat_id"><?php esc_html_e('Telegram Chat ID', 'notification-hub'); ?></label></th>
                    <td>
                        <input
                            name="nh_telegram_chat_id"
                            id="nh_telegram_chat_id"
                            type="text"
                            value="<?php echo esc_attr(get_option('nh_telegram_chat_id', '')); ?>"
                            class="regular-text"
                            <?php echo $can_telegram ? '' : 'disabled'; ?>
                        >

                        <p class="description">
                            <?php esc_html_e('Send a message to your bot, then run /getUpdates to find your chat ID.', 'notification-hub'); ?>
                        </p>

                        <?php if (!$can_telegram) : ?>
                            <p class="description">
                                <em><?php esc_html_e('This field is disabled because it’s only available in the Pro version.', 'notification-hub'); ?></em>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th><label for="nh_slack_webhook"><?php esc_html_e('Slack Webhook URL', 'notification-hub'); ?></label></th>
                    <td>
                        <input
                            name="nh_slack_webhook"
                            id="nh_slack_webhook"
                            type="url"
                            value="<?php echo esc_attr(get_option('nh_slack_webhook', '')); ?>"
                            class="regular-text"
                            <?php echo $can_slack ? '' : 'disabled'; ?>
                        >

                        <p class="description">
                            <?php esc_html_e('Use an Incoming Webhook from Slack → App Integrations → Webhooks.', 'notification-hub'); ?>
                        </p>

                        <?php if (!$can_slack) : ?>
                            <p class="description">
                                <em><?php esc_html_e('This field is disabled because it’s only available in the Pro version.', 'notification-hub'); ?></em>
                            </p>
                        <?php else : ?>
                            <p>
                                <a
                                    href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=slack&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=pro')); ?>"
                                    data-tab="pro"
                                    class="button nh-test-btn"
                                >
                                    <?php esc_html_e('Send Test to Slack', 'notification-hub'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>