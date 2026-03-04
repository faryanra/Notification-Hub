<?php
/**
 * Settings Page (modular)
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$can_telegram = class_exists('NH_License') ? NH_License::can('telegram') : false;
$can_slack    = class_exists('NH_License') ? NH_License::can('slack') : false;

$is_pro_addon = (class_exists('NH_License') && method_exists('NH_License', 'is_pro_addon_active'))
    ? NH_License::is_pro_addon_active()
    : (defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE);

$active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';

$channel = isset($_GET['nh_test']) ? sanitize_text_field(wp_unslash($_GET['nh_test'])) : '';
$success = isset($_GET['success']) ? sanitize_text_field(wp_unslash($_GET['success'])) : '';

$partials = NH_PLUGIN_DIR . 'templates/settings/partials/';
?>

<div class="wrap nh-settings-wrap">
    <h1><?php esc_html_e('Notification Hub - Settings', 'notification-hub'); ?></h1>

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

    <?php if ($active_tab === 'premium') : ?>
        <?php
        $tab_premium = $partials . 'tab-premium.php';
        if (file_exists($tab_premium)) {
            include $tab_premium;
        }
        ?>
    <?php endif; ?>
</div>
