<?php
/**
 * Hooks Presenter
 *
 * Renders custom hooks page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hooks_Presenter {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ) );
		}

		$repo  = new \Notification_Hub\Repositories\Custom_Hooks();
		$hooks = $repo->get_all();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Custom Hooks', 'notification-hub' ); ?></h1>

			<p><?php esc_html_e( 'Register custom WordPress action hooks to trigger notifications.', 'notification-hub' ); ?></p>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Action Name', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Channels', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Status', 'notification-hub' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $hooks ) ) : ?>
						<tr>
							<td colspan="4"><?php esc_html_e( 'No custom hooks found.', 'notification-hub' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $hooks as $hook ) : ?>
							<tr>
								<td><?php echo esc_html( $hook['title'] ?? '' ); ?></td>
								<td><code><?php echo esc_html( $hook['action_name'] ?? '' ); ?></code></td>
								<td><?php echo esc_html( $hook['channels'] ?? '' ); ?></td>
								<td><?php echo $hook['status'] ? esc_html__( 'Active', 'notification-hub' ) : esc_html__( 'Inactive', 'notification-hub' ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
