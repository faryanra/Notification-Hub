<?php
/**
 * Analytics Presenter
 *
 * Renders analytics/stats page.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Analytics_Presenter {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		$total_count  = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$unread_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 0" );
		$read_count   = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 1" );

		$by_source = $wpdb->get_results(
			"SELECT source, COUNT(*) as count FROM {$table} GROUP BY source ORDER BY count DESC LIMIT 10",
			ARRAY_A
		);

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Notification Analytics', 'notification-hub' ); ?></h1>

			<div class="nh-stats-grid">
				<div class="nh-stat-card">
					<h3><?php esc_html_e( 'Total Notifications', 'notification-hub' ); ?></h3>
					<p class="nh-stat-number"><?php echo (int) $total_count; ?></p>
				</div>

				<div class="nh-stat-card">
					<h3><?php esc_html_e( 'Unread', 'notification-hub' ); ?></h3>
					<p class="nh-stat-number"><?php echo (int) $unread_count; ?></p>
				</div>

				<div class="nh-stat-card">
					<h3><?php esc_html_e( 'Read', 'notification-hub' ); ?></h3>
					<p class="nh-stat-number"><?php echo (int) $read_count; ?></p>
				</div>
			</div>

			<h2><?php esc_html_e( 'By Source', 'notification-hub' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Source', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Count', 'notification-hub' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $by_source ) ) : ?>
						<tr>
							<td colspan="2"><?php esc_html_e( 'No data available.', 'notification-hub' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $by_source as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['source'] ?? '' ); ?></td>
								<td><?php echo (int) $row['count']; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
