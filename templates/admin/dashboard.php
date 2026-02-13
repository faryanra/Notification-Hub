<?php
/**
 * Dashboard Page Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Notification Hub Dashboard', 'notification-hub' ); ?></h1>

	<div class="nh-dashboard">
		<div class="nh-stats">
			<div class="nh-stat-card">
				<h3><?php esc_html_e( 'Total Notifications', 'notification-hub' ); ?></h3>
				<p class="nh-stat-number"><?php echo esc_html( count( $notifications ) ); ?></p>
			</div>

			<div class="nh-stat-card">
				<h3><?php esc_html_e( 'Unread', 'notification-hub' ); ?></h3>
				<p class="nh-stat-number">
					<?php
					echo esc_html(
						count(
							array_filter(
								$notifications,
								function( $n ) {
									return $n->status === 'unread';
								}
							)
						)
					);
					?>
				</p>
			</div>
		</div>

		<div class="nh-notifications-list">
			<h2><?php esc_html_e( 'Recent Notifications', 'notification-hub' ); ?></h2>

			<?php if ( empty( $notifications ) ) : ?>
				<p><?php esc_html_e( 'No notifications found.', 'notification-hub' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Title', 'notification-hub' ); ?></th>
							<th><?php esc_html_e( 'Type', 'notification-hub' ); ?></th>
							<th><?php esc_html_e( 'Status', 'notification-hub' ); ?></th>
							<th><?php esc_html_e( 'Date', 'notification-hub' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $notifications as $notification ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $notification->title ); ?></strong>
									<br>
									<small><?php echo esc_html( wp_trim_words( $notification->message, 10 ) ); ?></small>
								</td>
								<td><?php echo esc_html( $notification->type ); ?></td>
								<td>
									<span class="nh-status nh-status-<?php echo esc_attr( $notification->status ); ?>">
										<?php echo esc_html( ucfirst( $notification->status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( human_time_diff( strtotime( $notification->created_at ), current_time( 'timestamp' ) ) . ' ago' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
