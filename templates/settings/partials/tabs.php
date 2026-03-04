<?php
/**
 * Settings Tabs
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;
?>

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
