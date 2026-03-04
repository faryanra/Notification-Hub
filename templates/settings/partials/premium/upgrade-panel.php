<?php
/**
 * Premium Upgrade Panel
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="nh-upgrade-panel">
    <h2><?php esc_html_e('Premium Channels', 'notification-hub'); ?></h2>

    <p>
        <?php esc_html_e('Premium Channels are available in the Pro add-on.', 'notification-hub'); ?>
    </p>

    <p>
        <a class="button button-primary" href="#">
            <?php esc_html_e('Upgrade to Pro', 'notification-hub'); ?>
        </a>
    </p>
</div>
