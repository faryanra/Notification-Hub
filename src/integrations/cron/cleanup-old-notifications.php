<?php
/**
 * Cleanup Old Notifications Cron
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Cron;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleanup Old Notifications
 */
class Cleanup_Old_Notifications implements Integration_Interface {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function register() {
		add_action( 'nh_cron_cleanup', array( $this, 'handle' ) );
	}

	public function handle() {
		$retention_days = get_option( 'nh_retention_days', 30 );

		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}nh_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$retention_days
			)
		);
	}
}
