<?php
/**
 * Export CSV Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Helpers\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export CSV
 */
class Export_Csv {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle() {
		if ( ! Security::verify_nonce( $_GET['nonce'] ?? '', 'nh_export_csv' ) ) {
			wp_die( __( 'Invalid nonce', 'notification-hub' ) );
		}

		if ( ! Security::can( 'manage_options' ) ) {
			wp_die( __( 'Permission denied', 'notification-hub' ) );
		}

		$notifications = $this->repo->get_all();

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="notifications.csv"' );

		$output = fopen( 'php://output', 'w' );

		fputcsv( $output, array( 'ID', 'Title', 'Type', 'Status', 'Date' ) );

		foreach ( $notifications as $notification ) {
			fputcsv(
				$output,
				array(
					$notification->id,
					$notification->title,
					$notification->type,
					$notification->status,
					$notification->created_at,
				)
			);
		}

		fclose( $output );
		exit;
	}
}
