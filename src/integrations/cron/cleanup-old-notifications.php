<?php
/**
 * Cleanup Old Notifications Cron
 *
 * Deletes notifications older than retention days.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Cron;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cleanup_Old_Notifications implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'nh_cron_cleanup', array( $this, 'handle' ) );
	}

	public function handle() {
		$retention_days = (int) get_option( 'nh_retention_days', 90 );

		$repo = new \Notification_Hub\Repositories\Notifications();
		$deleted = $repo->delete_old( $retention_days );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'NH Cleanup: Deleted %d old notifications', $deleted ) );
		}
	}
}
