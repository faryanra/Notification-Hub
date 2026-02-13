<?php
/**
 * User Registered Event
 *
 * (Extracted from NH_Int_WP_Core::on_user_register)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User_Registered implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'user_register', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $user_id ) {
		$u = get_userdata( $user_id );
		if ( ! $u ) {
			return;
		}

		$title = sprintf(
			esc_html__( 'New user: %s', 'notification-hub' ),
			(string) $u->user_login
		);
		$message = esc_html( (string) $u->user_email );

		$context = array( 'user_id' => (int) $user_id );

		$e = array(
			'source'  => 'wp_core',
			'type'    => 'user_registered',
			'title'   => $title,
			'message' => $message,
			'context' => $context,
		);

		$db = $this->container->get_svc( 'db' );
		if ( $db && method_exists( $db, 'insert_notification' ) ) {
			$db->insert_notification( $e );
		}

		$notifier = $this->container->get_svc( 'notifier' );
		if ( $notifier && method_exists( $notifier, 'queue_send' ) ) {
			$payload = array(
				'title'   => $e['title'],
				'summary' => $e['message'],
				'source'  => $e['source'],
				'type'    => $e['type'],
				'context' => $e['context'],
				'link'    => function_exists( 'get_edit_user_link' ) ? (string) get_edit_user_link( (int) $user_id ) : '',
				'no_log'  => true,
			);

			$notifier->queue_send( 'email', $payload );
			$notifier->queue_send( 'telegram', $payload );
			$notifier->queue_send( 'slack', $payload );
		}
	}
}
