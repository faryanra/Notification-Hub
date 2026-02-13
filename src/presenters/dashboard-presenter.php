<?php
/**
 * Dashboard Presenter
 *
 * (Extracted from NH_Dashboard)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard_Presenter {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'notification-hub' ) );
		}

		$status_filter = isset( $_GET['filter_status'] )
			? sanitize_key( wp_unslash( $_GET['filter_status'] ) )
			: 'all';

		$allowed_status = array( 'all', 'unread', 'read', 'archived' );
		if ( ! in_array( $status_filter, $allowed_status, true ) ) {
			$status_filter = 'all';
		}

		$prev_seen = get_user_meta( get_current_user_id(), 'nh_last_seen_at', true );
		$prev_seen = $prev_seen ? (string) $prev_seen : '1970-01-01 00:00:00';

		$this->track_last_seen( $prev_seen );

		$this->render_header();
		$this->render_list( $status_filter );
		$this->render_footer();
	}

	private function track_last_seen( string $prev_seen ): void {
		if ( wp_script_is( 'nh-dashboard', 'enqueued' ) || wp_script_is( 'nh-dashboard', 'done' ) ) {
			wp_localize_script(
				'nh-dashboard',
				'nhSeen',
				array(
					'prev' => $prev_seen,
				)
			);
		}

		update_user_meta( get_current_user_id(), 'nh_last_seen_at', current_time( 'mysql' ) );
	}

	private function render_header(): void {
		echo '<div class="wrap">';
		echo '<div id="nh-table-loader" class="nh-table-loader" aria-hidden="true">';
		echo '<span class="spinner is-active nh-table-loader__spinner"></span>';
		echo '</div>';
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'Notifications Dashboard', 'notification-hub' ) . '</h1>';
		echo '<hr class="wp-header-end">';
	}

	private function render_list( string $filter ): void {
		$repo = new \Notification_Hub\Repositories\Notifications();

		$filters = array();
		if ( $filter === 'unread' ) {
			$filters['status'] = 0;
		} elseif ( $filter === 'read' ) {
			$filters['status'] = 1;
		}

		$items = $repo->get_list( $filters, 1, 50 );

		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Title', 'notification-hub' ) . '</th>';
		echo '<th>' . esc_html__( 'Source', 'notification-hub' ) . '</th>';
		echo '<th>' . esc_html__( 'Date', 'notification-hub' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'notification-hub' ) . '</th>';
		echo '</tr></thead>';

		echo '<tbody>';
		foreach ( $items as $item ) {
			echo '<tr>';
			echo '<td>' . esc_html( $item['title'] ?? '' ) . '</td>';
			echo '<td>' . esc_html( $item['source'] ?? '' ) . '</td>';
			echo '<td>' . esc_html( $item['created_at'] ?? '' ) . '</td>';
			echo '<td>' . ( $item['status'] ? esc_html__( 'Read', 'notification-hub' ) : esc_html__( 'Unread', 'notification-hub' ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}

	private function render_footer(): void {
		echo '</div>';
	}
}
