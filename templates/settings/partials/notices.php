<?php
/**
 * Settings Notices
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$nh_test_channel = isset($_GET['nh_test']) ? sanitize_key(wp_unslash($_GET['nh_test'])) : '';
$nh_test_success = isset($_GET['success']) ? sanitize_text_field(wp_unslash($_GET['success'])) : '';
$nh_test_error = isset($_GET['nh_test_error']) ? sanitize_text_field(rawurldecode((string) wp_unslash($_GET['nh_test_error']))) : '';
$nh_test_http = isset($_GET['nh_test_http']) ? absint($_GET['nh_test_http']) : 0;
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
            <?php if ($nh_test_error !== '') : ?>
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: %1$s: error text, %2$s: HTTP status code */
                            __('Details: %1$s%2$s', 'notification-hub'),
                            $nh_test_error,
                            $nh_test_http > 0 ? sprintf(__(' (HTTP %d)', 'notification-hub'), $nh_test_http) : ''
                        )
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_GET['settings-updated'])) : ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Settings saved successfully.', 'notification-hub'); ?></p>
    </div>
<?php endif; ?>

