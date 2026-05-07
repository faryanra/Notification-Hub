<?php
/**
 * Settings Page (modular)
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
if (!in_array($active_tab, ['general', 'channels'], true)) {
    $active_tab = 'general';
}

$partials = NH_PLUGIN_DIR . 'templates/settings/partials/';
?>

<div class="wrap nh-settings-wrap">
    <h1><?php esc_html_e('HelloCode Notification Hub - Settings', 'notification-hub'); ?></h1>

    <?php
    $notices = $partials . 'notices.php';
    if (file_exists($notices)) {
        include $notices;
    }

    $tabs = $partials . 'tabs.php';
    if (file_exists($tabs)) {
        include $tabs;
    }
    ?>

    <?php if ($active_tab === 'general') : ?>
        <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
            <?php settings_fields('nh_settings_general'); ?>
            <?php do_settings_sections('nh_settings_general'); ?>

            <?php
            $tab_general = $partials . 'tab-general.php';
            if (file_exists($tab_general)) {
                include $tab_general;
            }

            submit_button();
            ?>
        </form>
    <?php endif; ?>

    <?php if ($active_tab === 'channels') : ?>
        <?php
        $tab_channels = $partials . 'tab-channels.php';
        if (file_exists($tab_channels)) {
            include $tab_channels;
        }
        ?>
    <?php endif; ?>
</div>

