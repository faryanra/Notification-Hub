<?php
/**
 * Settings Presenter
 *
 * Renders settings page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Presenter {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Notification Hub Settings', 'notification-hub' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'nh_settings' ); ?>
				<?php do_settings_sections( 'nh_settings' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="nh_email_recipients"><?php esc_html_e( 'Email Recipients', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="email" id="nh_email_recipients" name="nh_email_recipients" value="<?php echo esc_attr( get_option( 'nh_email_recipients', get_option( 'admin_email' ) ) ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Email address to receive notifications.', 'notification-hub' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="nh_retention_days"><?php esc_html_e( 'Retention Days', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="number" id="nh_retention_days" name="nh_retention_days" value="<?php echo esc_attr( get_option( 'nh_retention_days', 90 ) ); ?>" min="1" max="365" />
							<p class="description"><?php esc_html_e( 'Delete notifications older than this many days.', 'notification-hub' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
