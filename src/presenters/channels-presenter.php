<?php
/**
 * Channels Presenter
 *
 * Renders channels configuration page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Channels_Presenter {

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
			<h1><?php esc_html_e( 'Notification Channels', 'notification-hub' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'nh_channels' ); ?>

				<h2><?php esc_html_e( 'Email Channel', 'notification-hub' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="nh_email_to"><?php esc_html_e( 'Recipient Email', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="email" id="nh_email_to" name="nh_email_to" value="<?php echo esc_attr( get_option( 'nh_email_to', get_option( 'admin_email' ) ) ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Telegram Channel (Premium)', 'notification-hub' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="nh_telegram_bot_token"><?php esc_html_e( 'Bot Token', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="text" id="nh_telegram_bot_token" name="nh_telegram_bot_token" value="<?php echo esc_attr( get_option( 'nh_telegram_bot_token', '' ) ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="nh_telegram_chat_id"><?php esc_html_e( 'Chat ID', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="text" id="nh_telegram_chat_id" name="nh_telegram_chat_id" value="<?php echo esc_attr( get_option( 'nh_telegram_chat_id', '' ) ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Slack Channel (Premium)', 'notification-hub' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="nh_slack_webhook_url"><?php esc_html_e( 'Webhook URL', 'notification-hub' ); ?></label>
						</th>
						<td>
							<input type="url" id="nh_slack_webhook_url" name="nh_slack_webhook_url" value="<?php echo esc_attr( get_option( 'nh_slack_webhook_url', '' ) ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
