<?php
/**
 * Notification Dispatcher Service
 *
 * Dispatches notifications to multiple channels.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification_Dispatcher Class
 */
class Notification_Dispatcher {

	/**
	 * Dispatch notification to multiple channels.
	 *
	 * @param array $channels Channel slugs (e.g., 'email', 'telegram', 'slack').
	 * @param array $payload  Notification payload.
	 * @return void
	 */
	public function dispatch( array $channels, array $payload ) {
		// TODO: Implement queue system.
		// For now, just a stub to prevent fatal errors.

		/**
		 * Hook for extensibility.
		 *
		 * @param array $channels Channel slugs.
		 * @param array $payload  Notification payload.
		 */
		do_action( 'nh_dispatch_notification', $channels, $payload );
	}
}
