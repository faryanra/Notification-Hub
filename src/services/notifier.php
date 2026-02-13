<?php
/**
 * Notifier Service
 *
 * (Extracted from NH_Notifier_Dispatcher)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifier {

	private $container;
	private $queue_processor;

	public function __construct( $container ) {
		$this->container       = $container;
		$this->queue_processor = new Queue_Processor( $container );
	}

	public function queue_send( string $channel, array $payload = array() ): bool {
		$channel = sanitize_key( $channel );
		return (bool) $this->queue_processor->queue_send( $channel, $payload );
	}

	public function send_now( string $channel, array $payload = array() ): bool {
		$channel = sanitize_key( $channel );

		if ( ! $this->check_network_policy( $channel, $payload ) ) {
			return false;
		}

		$success = $this->dispatch( $channel, $payload );

		if ( isset( $payload['notification_id'] ) ) {
			$this->queue_processor->log_delivery_status(
				(int) $payload['notification_id'],
				$channel,
				(bool) $success,
				$success ? '' : esc_html__( 'Delivery failed', 'notification-hub' )
			);
		}

		return (bool) $success;
	}

	private function dispatch( string $channel, array $payload ): bool {
		switch ( $channel ) {
			case 'email':
				if ( ! empty( $payload['override_email_to'] ) && is_string( $payload['override_email_to'] ) ) {
					$payload['to'] = sanitize_email( $payload['override_email_to'] );
				}

				$email_sender = $this->container->get_svc( 'email_sender' );
				if ( ! $email_sender ) {
					return false;
				}

				return (bool) $email_sender->send( $payload );

			case 'telegram':
				return $this->send_pro_channel( 'telegram', 'Telegram', $payload );

			case 'slack':
				return $this->send_pro_channel( 'slack', 'Slack', $payload );

			default:
				return false;
		}
	}

	private function send_pro_channel( string $cap, string $name, array $payload ): bool {
		// TODO: Check license via NH_License::can()
		return false;
	}

	private function check_network_policy( string $channel, array &$payload ): bool {
		if ( ! is_multisite() ) {
			return true;
		}

		$policy = get_site_option( 'nh_network_policy', array() );
		$policy = is_array( $policy ) ? $policy : array();

		if ( ! empty( $policy['channels'] ) && is_array( $policy['channels'] ) && ! in_array( $channel, $policy['channels'], true ) ) {
			return false;
		}

		if ( $channel === 'email' && ! empty( $policy['email_to'] ) && is_string( $policy['email_to'] ) ) {
			$payload['override_email_to'] = sanitize_email( $policy['email_to'] );
		}

		return true;
	}
}
