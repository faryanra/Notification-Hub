<?php
/**
 * Export CSV Route
 *
 * Handler for CSV export.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Export_CSV {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'admin_post_nh_export_csv', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized', 403 );
		}

		check_admin_referer( 'nh_export_csv' );

		global $wpdb;
		$table = $wpdb->prefix . 'nh_notifications';

		$results = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 1000", ARRAY_A );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=notifications-' . gmdate( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		if ( ! empty( $results ) ) {
			fputcsv( $output, array_keys( $results[0] ) );

			foreach ( $results as $row ) {
				fputcsv( $output, $row );
			}
		}

		fclose( $output );
		exit;
	}
}
