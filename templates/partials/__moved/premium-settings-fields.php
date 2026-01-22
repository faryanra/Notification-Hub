<?php
/**
 * Premium Settings Fields markup (moved)
 *
 * Premium-only UI partial for Telegram / Slack settings.
 *
 * Expects variables:
 * - bool $can_telegram
 * - bool $can_slack
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

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
                <p class="description"><em><?php esc_html_e('This field is disabled because it’s only available in the Premium version.', 'notification-hub'); ?></em></p>
            <?php else : ?>
                <p>
                    <a
                        href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=telegram&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=premium')); ?>"
                        data-tab="premium"
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
                <p class="description"><em><?php esc_html_e('This field is disabled because it’s only available in the Premium version.', 'notification-hub'); ?></em></p>
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
                <p class="description"><em><?php esc_html_e('This field is disabled because it’s only available in the Premium version.', 'notification-hub'); ?></em></p>
            <?php else : ?>
                <p>
                    <a
                        href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=slack&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=premium')); ?>"
                        data-tab="premium"
                        class="button nh-test-btn"
                    >
                        <?php esc_html_e('Send Test to Slack', 'notification-hub'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </td>
    </tr>
</table>
