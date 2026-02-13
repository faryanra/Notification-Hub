<?php
/**
 * Slack Sender (Premium)
 *
 * Sends notifications via Slack.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Channels;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Slack_Sender {

	public function supports( string $channel ): bool {
		return $channel === 'slack';
	}

	public function send( array $payload ): bool {
		$webhook_url = get_option( 'nh_slack_webhook_url', '' );

		if ( empty( $webhook_url ) ) {
			return false;
		}

		$title   = isset( $payload['title'] ) ? sanitize_text_field( $payload['title'] ) : '';
		$message = isset( $payload['summary'] ) ? wp_strip_all_tags( $payload['summary'] ) : '';

		$text = $title;
		if ( $message ) {
			$text .= "\n" . $message;
		}

		$response = wp_remote_post(
			$webhook_url,
			array(
				'body' => wp_json_encode(
					array(
						'text' => $text,
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		return $code === 200;
	}
}
