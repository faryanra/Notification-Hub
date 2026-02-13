<?php
/**
 * General Settings Tab Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2><?php esc_html_e( 'General Settings', 'notification-hub' ); ?></h2>

<form method="post" action="options.php">
	<?php settings_fields( 'nh_general_settings' ); ?>
	<?php do_settings_sections( 'notification-hub-settings' ); ?>
	<?php submit_button(); ?>
</form>
