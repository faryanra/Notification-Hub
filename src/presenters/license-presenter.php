<?php
/**
 * License Presenter
 *
 * Renders license page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License_Presenter {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ) );
		}

		$license_key = get_option( 'nh_license_key', '' );
		$status      = get_option( 'nh_license_status', '' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'License Settings', 'notification-hub' ); ?></h1>

			<?php if ( $status === 'active' ) : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'License is active!', 'notification-hub' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'nh_license_action' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="license_key"><?php esc_html_e( 'License Key', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="text" id="license_key" name="license_key" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Enter your premium license key.', 'notification-hub' ); ?></p>
						</td>
					</tr>
				</table>

				<?php if ( $status === 'active' ) : ?>
					<button type="submit" name="deactivate" class="button button-secondary"><?php esc_html_e( 'Deactivate License', 'notification-hub' ); ?></button>
				<?php else : ?>
					<button type="submit" name="activate" class="button button-primary"><?php esc_html_e( 'Activate License', 'notification-hub' ); ?></button>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}
}
