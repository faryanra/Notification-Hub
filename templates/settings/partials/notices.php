<?php
/**
 * Settings Notices
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$nh_test_channel = isset($_GET['nh_test']) ? sanitize_key(wp_unslash($_GET['nh_test'])) : '';
$nh_test_success = isset($_GET['success']) ? sanitize_text_field(wp_unslash($_GET['success'])) : '';
?>

<?php if ($nh_test_success !== '') : ?>
    <?php if ((string) $nh_test_success === '1') : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                echo sprintf(
                    /* translators: %s: channel name (email/telegram/slack) */
                    esc_html__('Test sent successfully to %s.', 'notification-hub'),
                    '<strong>' . esc_html($nh_test_channel !== '' ? $nh_test_channel : 'email') . '</strong>'
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
                    '<strong>' . esc_html($nh_test_channel !== '' ? $nh_test_channel : 'email') . '</strong>'
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
