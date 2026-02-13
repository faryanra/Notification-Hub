<?php
/**
 * Notification Dispatcher
 *
 * Dispatches notifications to channels.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Dispatcher
 */
class Notification_Dispatcher {

	/**
	 * Dispatch notification.
	 *
	 * @param int    $notification_id Notification ID.
	 * @param string $type            Notification type.
	 * @return void
	 */
	public function dispatch( $notification_id, $type ) {
		do_action( 'nh_dispatch_notification', $notification_id, $type );
	}
}
