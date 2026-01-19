<?php
/**
 * Premium Upgrade Panel (shown when Premium addon is not installed).
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="nh-pro-upgrade">
    <h2><?php esc_html_e('Unlock Premium Channels', 'notification-hub'); ?></h2>

    <p><?php esc_html_e('Telegram and Slack integrations are part of Notification Hub Premium. Install the Premium addon to enable these channels and activate your license.', 'notification-hub'); ?></p>

    <div class="nh-pro-features">
        <div class="nh-pro-feature">
            <h3><?php esc_html_e('Telegram Notifications', 'notification-hub'); ?></h3>
            <p><?php esc_html_e('Send real-time alerts to your Telegram chats using a bot token and chat ID.', 'notification-hub'); ?></p>
        </div>

        <div class="nh-pro-feature">
            <h3><?php esc_html_e('Slack Notifications', 'notification-hub'); ?></h3>
            <p><?php esc_html_e('Post notifications to any channel via Slack Incoming Webhooks.', 'notification-hub'); ?></p>
        </div>

        <div class="nh-pro-feature">
            <h3><?php esc_html_e('License Activation', 'notification-hub'); ?></h3>
            <p><?php esc_html_e('Activate your Premium license, view status, and keep your setup working reliably.', 'notification-hub'); ?></p>
        </div>
    </div>

    <p>
        <a class="button button-primary" href="#" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('Get Notification Hub Premium', 'notification-hub'); ?>
        </a>
        <a class="button" href="#" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('How to install Premium', 'notification-hub'); ?>
        </a>
    </p>

    <p class="description">
        <?php esc_html_e('Already have Premium? Install and activate the addon, then return here to enter your license.', 'notification-hub'); ?>
    </p>
</div>
