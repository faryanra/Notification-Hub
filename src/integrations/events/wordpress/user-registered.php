<?php
/**
 * User Registered Event
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
 * User Registered
 */
class User_Registered implements Integration_Interface {

	/**
	 * Notifications repository.
	 *
	 * @var Notifications
	 */
	private $repo;

	/**
	 * Notification dispatcher.
	 *
	 * @var Notification_Dispatcher
	 */
	private $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param Notifications            $repo       Notifications repository.
	 * @param Notification_Dispatcher $dispatcher Dispatcher.
	 */
	public function __construct( Notifications $repo, Notification_Dispatcher $dispatcher ) {
		$this->repo       = $repo;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'user_register', array( $this, 'handle' ), 10, 1 );
	}

	/**
	 * Handle user registration.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function handle( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		$notification_id = $this->repo->create(
			array(
				'title'   => __( 'New user registered', 'notification-hub' ),
				'message' => sprintf(
					__( 'User %s (%s) has registered', 'notification-hub' ),
					$user->user_login,
					$user->user_email
				),
				'type'    => 'user_registered',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'user_registered' );
		}
	}
}
