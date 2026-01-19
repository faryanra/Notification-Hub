<?php
/**
 * Pro Upgrade Panel (Free)
 *
 * Shown when the Pro addon is not installed.
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="nh-upgrade-wrap">
    <div class="nh-upgrade-hero">
        <div class="nh-upgrade-hero__content">
            <h2 class="nh-upgrade-hero__title"><?php esc_html_e('Unlock Pro Channels', 'notification-hub'); ?></h2>
            <p class="nh-upgrade-hero__subtitle">
                <?php esc_html_e('Telegram and Slack integrations are part of Notification Hub Pro. Install the Pro addon to enable these channels and activate your license.', 'notification-hub'); ?>
            </p>

            <div class="nh-upgrade-hero__cta">
                <a href="#" class="button button-primary button-hero">
                    <?php esc_html_e('Get Notification Hub Pro', 'notification-hub'); ?>
                </a>
                <a href="#" class="button button-secondary">
                    <?php esc_html_e('How to install Pro', 'notification-hub'); ?>
                </a>
            </div>

            <div class="nh-upgrade-features">
                <div class="nh-upgrade-feature">
                    <h3><?php esc_html_e('Telegram Notifications', 'notification-hub'); ?></h3>
                    <p><?php esc_html_e('Send real-time alerts to your Telegram chats using a bot token and chat ID.', 'notification-hub'); ?></p>
                </div>

                <div class="nh-upgrade-feature">
                    <h3><?php esc_html_e('Slack Notifications', 'notification-hub'); ?></h3>
                    <p><?php esc_html_e('Post notifications to any channel via Slack Incoming Webhooks.', 'notification-hub'); ?></p>
                </div>

                <div class="nh-upgrade-feature">
                    <h3><?php esc_html_e('License Activation', 'notification-hub'); ?></h3>
                    <p><?php esc_html_e('Activate your Pro license, view status, and keep your setup working reliably.', 'notification-hub'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="nh-upgrade-note">
        <p class="description">
            <?php esc_html_e('Already have Pro? Install and activate the Pro addon, then return here to enter your license.', 'notification-hub'); ?>
        </p>
    </div>
</div>
