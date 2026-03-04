<?php
/**
 * Settings Tab: Premium
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$premium_root = NH_PLUGIN_DIR . 'templates/settings/partials/premium/';

// We'll show the license box even if pro addon isn't active, so user can set server/key.
$license_box = $premium_root . 'license-box.php';
?>

<div id="nh-tab-premium" class="nh-tab is-active" data-tab="premium">
    <?php
    if (file_exists($license_box)) {
        include $license_box;
    }

    if (!$is_pro_addon) {
        $upgrade_partial = $premium_root . 'upgrade-panel.php';
        if (file_exists($upgrade_partial)) {
            include $upgrade_partial;
        }
    } else {
        $fields_partial = $premium_root . 'settings-fields.php';
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
            <?php settings_fields('nh_settings_premium'); ?>
            <?php do_settings_sections('nh_settings_premium'); ?>

            <?php
            if (file_exists($fields_partial)) {
                include $fields_partial;
            }

            submit_button(__('Save Changes', 'notification-hub'));
            ?>
        </form>
        <?php
    }
    ?>
</div>
