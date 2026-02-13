<?php
/**
 * Telegram Sender (Premium)
 *
 * Sends notifications via Telegram.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Channels;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Telegram_Sender {

	public function supports( string $channel ): bool {
		return $channel === 'telegram';
	}

	public function send( array $payload ): bool {
		$bot_token = get_option( 'nh_telegram_bot_token', '' );
		$chat_id   = get_option( 'nh_telegram_chat_id', '' );

		if ( empty( $bot_token ) || empty( $chat_id ) ) {
			return false;
		}

		$title   = isset( $payload['title'] ) ? sanitize_text_field( $payload['title'] ) : '';
		$message = isset( $payload['summary'] ) ? wp_strip_all_tags( $payload['summary'] ) : '';

		$text = $title;
		if ( $message ) {
			$text .= "\n\n" . $message;
		}

		$url = sprintf(
			'https://api.telegram.org/bot%s/sendMessage',
			$bot_token
		);

		$response = wp_remote_post(
			$url,
			array(
				'body' => array(
					'chat_id' => $chat_id,
					'text'    => $text,
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
