<?php
/**
 * Telegram Channel Integration (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Integrations\Telegram;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Channel implements Integration_Interface {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function register() {
		add_action( 'nh_notification_created', array( $this, 'send' ), 10, 2 );
	}

	public function send( $notification_id, $type ) {
		$settings = get_option( 'nh_telegram_settings', array() );

		if ( empty( $settings['bot_token'] ) || empty( $settings['chat_id'] ) ) {
			return;
		}

		$notification = $this->repo->get( $notification_id );

		if ( ! $notification ) {
			return;
		}

		$message = "<b>{$notification->title}</b>\n\n{$notification->message}";

		wp_remote_post(
			"https://api.telegram.org/bot{$settings['bot_token']}/sendMessage",
			array(
				'body' => array(
					'chat_id'    => $settings['chat_id'],
					'text'       => $message,
					'parse_mode' => 'HTML',
				),
			)
		);
	}
}
