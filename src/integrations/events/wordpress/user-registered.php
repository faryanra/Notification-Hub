<?php
/**
 * User Registered Event
 *
 * Listens for new user registrations and creates notifications.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Services\Notification_Dispatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User_Registered Class
 */
class User_Registered implements Integration_Interface {

	/**
	 * Notifications repository.
	 *
	 * @var Notifications
	 */
	private $notifications_repo;

	/**
	 * Notification dispatcher.
	 *
	 * @var Notification_Dispatcher
	 */
	private $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param Notifications           $notifications_repo Notifications repository.
	 * @param Notification_Dispatcher $dispatcher         Notification dispatcher.
	 */
	public function __construct( Notifications $notifications_repo, Notification_Dispatcher $dispatcher ) {
		$this->notifications_repo = $notifications_repo;
		$this->dispatcher         = $dispatcher;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'user_register', array( $this, 'on_user_register' ), 10, 1 );
	}

	/**
	 * Handle user registration.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function on_user_register( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$title = sprintf(
			/* translators: %s: Username. */
			esc_html__( 'New user: %s', 'notification-hub' ),
			(string) $user->user_login
		);

		$message = esc_html( (string) $user->user_email );

		$context = array( 'user_id' => (int) $user_id );

		// Insert notification.
		$notification_id = $this->notifications_repo->insert(
			array(
				'source'  => 'wp_core',
				'type'    => 'user_registered',
				'title'   => $title,
				'message' => $message,
				'context' => $context,
			)
		);

		// Dispatch to channels.
		if ( $notification_id ) {
			$payload = array(
				'title'   => $title,
				'summary' => $message,
				'source'  => 'wp_core',
				'type'    => 'user_registered',
				'context' => $context,
				'link'    => function_exists( 'get_edit_user_link' ) ? (string) get_edit_user_link( (int) $user_id ) : '',
			);

			$this->dispatcher->dispatch( array( 'email', 'telegram', 'slack' ), $payload );
		}
	}
}
