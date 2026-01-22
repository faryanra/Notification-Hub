<?php
/**
 * Settings Notices
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;
?>

<?php if (!empty($success)) : ?>
    <?php if ((string) $success === '1') : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                echo sprintf(
                    /* translators: %s: channel name (email/telegram/slack) */
                    esc_html__('Test sent successfully to %s.', 'notification-hub'),
                    '<strong>' . esc_html(!empty($channel) ? $channel : 'email') . '</strong>'
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
                    '<strong>' . esc_html(!empty($channel) ? $channel : 'email') . '</strong>'
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
