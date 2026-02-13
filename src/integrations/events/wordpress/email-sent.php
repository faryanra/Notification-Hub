<?php
/**
 * Email Sent Event
 *
 * Monitors wp_mail filter.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Sent implements Integration_Interface {

	public function register(): void {
		add_filter( 'wp_mail', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $args ) {
		// TODO: Log email sent events
		return $args;
	}
}
