<?php
/**
 * Channel Factory
 *
 * Creates channel sender instances.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Channel_Factory {

	public static function create( string $channel, $container ) {
		switch ( $channel ) {
			case 'email':
				return new \Notification_Hub\Integrations\Channels\Email_Sender( $container );

			case 'telegram':
				if ( class_exists( '\Notification_Hub\Premium\Channels\Telegram_Sender' ) ) {
					return new \Notification_Hub\Premium\Channels\Telegram_Sender();
				}
				break;

			case 'slack':
				if ( class_exists( '\Notification_Hub\Premium\Channels\Slack_Sender' ) ) {
					return new \Notification_Hub\Premium\Channels\Slack_Sender();
				}
				break;
		}

		return null;
	}

	public static function get_available_channels(): array {
		$channels = array(
			'email' => __( 'Email', 'notification-hub' ),
		);

		if ( class_exists( '\Notification_Hub\Premium\License_Manager' ) ) {
			$license = \Notification_Hub\Premium\License_Manager::instance();

			if ( $license->can( 'telegram' ) ) {
				$channels['telegram'] = __( 'Telegram', 'notification-hub' );
			}

			if ( $license->can( 'slack' ) ) {
				$channels['slack'] = __( 'Slack', 'notification-hub' );
			}
		}

		return $channels;
	}
}
