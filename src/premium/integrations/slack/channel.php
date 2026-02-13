<?php
/**
 * Slack Channel Integration (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Integrations\Slack;

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
		$settings = get_option( 'nh_slack_settings', array() );

		if ( empty( $settings['webhook_url'] ) ) {
			return;
		}

		$notification = $this->repo->get( $notification_id );

		if ( ! $notification ) {
			return;
		}

		$payload = array(
			'text'        => $notification->title,
			'attachments' => array(
				array(
					'text' => $notification->message,
				),
			),
		);

		wp_remote_post(
			$settings['webhook_url'],
			array(
				'body'    => wp_json_encode( $payload ),
				'headers' => array( 'Content-Type' => 'application/json' ),
			)
		);
	}
}
