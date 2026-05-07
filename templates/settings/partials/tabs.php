<?php
/**
 * Settings Tabs
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$general_tab_class = $active_tab === 'general' ? 'nav-tab-active' : '';
$channels_tab_class = $active_tab === 'channels' ? 'nav-tab-active' : '';
?>

<h2 class="nav-tab-wrapper nh-settings-tabs" data-active-tab="<?php echo esc_attr($active_tab); ?>">
    <a
        href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=general')); ?>"
        class="nav-tab <?php echo esc_attr($general_tab_class); ?>"
        data-tab="general"
    >
        <?php esc_html_e('General', 'notification-hub'); ?>
    </a>

    <a
        href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=channels')); ?>"
        class="nav-tab <?php echo esc_attr($channels_tab_class); ?>"
        data-tab="channels"
    >
        <?php esc_html_e('Channels', 'notification-hub'); ?>
    </a>
</h2>

