<?php
/**
 * Logs Presenter
 *
 * Renders notification logs page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logs_Presenter {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ) );
		}

		$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 50;

		$repo = new \Notification_Hub\Repositories\Notifications();
		$logs = $repo->get_list( array(), $page, $per_page );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Notification Logs', 'notification-hub' ); ?></h1>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Title', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Source', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Type', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Priority', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Status', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Date', 'notification-hub' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr>
							<td colspan="7"><?php esc_html_e( 'No logs found.', 'notification-hub' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo (int) $log['id']; ?></td>
								<td><?php echo esc_html( $log['title'] ?? '' ); ?></td>
								<td><?php echo esc_html( $log['source'] ?? '' ); ?></td>
								<td><?php echo esc_html( $log['type'] ?? '' ); ?></td>
								<td><?php echo (int) ( $log['priority'] ?? 50 ); ?></td>
								<td><?php echo $log['status'] ? esc_html__( 'Read', 'notification-hub' ) : esc_html__( 'Unread', 'notification-hub' ); ?></td>
								<td><?php echo esc_html( $log['created_at'] ?? '' ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
