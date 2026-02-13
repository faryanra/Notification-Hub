<?php
/**
 * Process Queue Cron
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Cron;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process Queue
 */
class Process_Queue implements Integration_Interface {

	public function register() {
		add_action( 'nh_process_queue', array( $this, 'handle' ) );
	}

	public function handle() {
		// TODO: Implement queue processing logic
	}
}
