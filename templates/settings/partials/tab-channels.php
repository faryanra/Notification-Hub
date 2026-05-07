<?php
/**
 * Settings Tab: Channels
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

$channels_root = NH_PLUGIN_DIR . 'templates/settings/partials/channels/';
$fields_partial = $channels_root . 'settings-fields.php';
?>

<div id="nh-tab-channels" class="nh-tab is-active" data-tab="channels">
    <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
        <?php settings_fields('nh_settings_channels'); ?>
        <?php do_settings_sections('nh_settings_channels'); ?>

        <?php
        if (file_exists($fields_partial)) {
            include $fields_partial;
        }

        submit_button(__('Save Changes', 'notification-hub'));
        ?>
    </form>
</div>

