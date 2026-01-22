<?php
/**
 * Settings Tab: Premium
 *
 * Rendered only when active tab is premium.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;
?>

<div
    id="nh-tab-premium"
    class="nh-tab is-active"
    data-tab="premium"
>
    <?php
    $partials_root = NH_PLUGIN_DIR . 'templates/partials/';

    if (!$is_pro_addon) {
        $upgrade_partial = $partials_root . 'premium-upgrade-panel.php';
        if (file_exists($upgrade_partial)) {
            include $upgrade_partial;
        }
    } else {
        $fields_partial = $partials_root . 'premium-settings-fields.php';
        if (file_exists($fields_partial)) {
            include $fields_partial;
        }
    }
    ?>
</div>
