<?php
/**
 * Settings Template
 *
 * Renders Notification Hub settings page (General / Pro tabs).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

$can_telegram = class_exists('NH_License') ? NH_License::can('telegram') : false;
$can_slack    = class_exists('NH_License') ? NH_License::can('slack') : false;

$is_pro_addon = (class_exists('NH_License') && method_exists('NH_License', 'is_pro_addon_active'))
    ? NH_License::is_pro_addon_active()
    : (defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE);

$active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';

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

    <h2 class="nav-tab-wrapper nh-settings-tabs" data-active-tab="<?php echo esc_attr($active_tab); ?>" data-pro-addon="<?php echo $is_pro_addon ? '1' : '0'; ?>">
        <a
            href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=general')); ?>"
            class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"
            data-tab="general"
        >
            <?php esc_html_e('General', 'notification-hub'); ?>
        </a>

        <a
            href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=premium')); ?>"
            class="nav-tab <?php echo $active_tab === 'premium' ? 'nav-tab-active' : ''; ?>"
            data-tab="premium"
        >
            <?php esc_html_e('Premium Channels', 'notification-hub'); ?>
        </a>
    </h2>

    <?php
    // Premium UI partials: prefixed with "premium-" for easy extraction into Premium zip.
    if ($is_pro_addon) {
        $license_partial = NH_PLUGIN_DIR . 'templates/partials/premium-license-box.php';
        if (file_exists($license_partial)) {
            include $license_partial;
        }

        // Optional debug panel for advanced troubleshooting.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $debug_partial = NH_PLUGIN_DIR . 'templates/partials/premium-license-debug-panel.php';
            if (file_exists($debug_partial)) {
                include $debug_partial;
            }
        }
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
            id="nh-tab-premium"
            class="nh-tab <?php echo $active_tab === 'premium' ? 'is-active' : ''; ?>"
            data-tab="premium"
        >
            <?php
            if (!$is_pro_addon) {
                $upgrade_partial = NH_PLUGIN_DIR . 'templates/partials/premium-upgrade-panel.php';
                if (file_exists($upgrade_partial)) {
                    include $upgrade_partial;
                }
            } else {
                $fields_partial = NH_PLUGIN_DIR . 'templates/partials/premium-settings-fields.php';
                if (file_exists($fields_partial)) {
                    include $fields_partial;
                }
            }
            ?>
        </div>

        <?php
        // Don't show Save Changes in the Premium tab when Premium addon isn't installed.
        if (!($active_tab === 'premium' && !$is_pro_addon)) {
            submit_button();
        }
        ?>
    </form>
</div>