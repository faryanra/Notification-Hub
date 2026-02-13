<?php
/**
 * Settings Page Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Notification Hub Settings', 'notification-hub' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'notification_hub_settings' ); ?>
		<?php do_settings_sections( 'notification_hub_settings' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="nh_email_enabled"><?php esc_html_e( 'Enable Email Notifications', 'notification-hub' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="nh_email_enabled" name="nh_email_enabled" value="1" <?php checked( get_option( 'nh_email_enabled', true ), true ); ?>>
					<p class="description"><?php esc_html_e( 'Send email notifications for events', 'notification-hub' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="nh_admin_email"><?php esc_html_e( 'Admin Email', 'notification-hub' ); ?></label>
				</th>
				<td>
					<input type="email" id="nh_admin_email" name="nh_admin_email" value="<?php echo esc_attr( get_option( 'nh_admin_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Email address to receive notifications', 'notification-hub' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="nh_retention_days"><?php esc_html_e( 'Retention Days', 'notification-hub' ); ?></label>
				</th>
				<td>
					<input type="number" id="nh_retention_days" name="nh_retention_days" value="<?php echo esc_attr( get_option( 'nh_retention_days', 30 ) ); ?>" class="small-text" min="1">
					<p class="description"><?php esc_html_e( 'Number of days to keep old notifications', 'notification-hub' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
