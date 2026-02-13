<?php
/**
 * Premium Settings Fields Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2><?php esc_html_e( 'Premium Features', 'notification-hub' ); ?></h2>

<?php settings_fields( 'nh_premium_settings' ); ?>
<?php do_settings_sections( 'notification-hub-premium' ); ?>
