<?php
/**
 * Email Sender Channel
 *
 * (Extracted from NH_Email)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Channels;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Sender implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		// Email sender doesn't need hooks
		// Called directly by notifier service
	}

	public function supports( $channel ): bool {
		return $channel === 'email';
	}

	public function send( $payload ): bool {
		$payload = is_array( $payload ) ? $payload : array();

		$to = isset( $payload['to'] ) && is_string( $payload['to'] ) && $payload['to'] !== ''
			? $payload['to']
			: get_option( 'nh_email_to', get_option( 'admin_email' ) );

		$subject = isset( $payload['subject'] ) && is_string( $payload['subject'] ) && $payload['subject'] !== ''
			? $payload['subject']
			: esc_html__( '[NH] Notification', 'notification-hub' );

		$body = isset( $payload['body'] ) && is_string( $payload['body'] )
			? $payload['body']
			: '';

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $to, $subject, wp_kses_post( $body ), $headers );
	}
}
