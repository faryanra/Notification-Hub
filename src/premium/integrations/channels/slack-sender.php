<?php
/**
 * Slack Sender (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Integrations\Channels;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slack Sender
 */
class Slack_Sender implements Integration_Interface {

	public function register() {
		add_action( 'nh_notification_created', array( $this, 'send' ), 10, 2 );
	}

	public function send( $notification_id, $type ) {
		// TODO: Implement Slack sending logic
	}
}
