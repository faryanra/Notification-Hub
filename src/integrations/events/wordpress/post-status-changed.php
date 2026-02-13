<?php
/**
 * Post Status Changed Event
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
 * Post Status Changed
 */
class Post_Status_Changed implements Integration_Interface {

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
		add_action( 'transition_post_status', array( $this, 'handle' ), 10, 3 );
	}

	/**
	 * Handle post status change.
	 *
	 * @param string   $new_status New status.
	 * @param string   $old_status Old status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public function handle( $new_status, $old_status, $post ) {
		if ( $old_status === $new_status ) {
			return;
		}

		if ( $new_status !== 'publish' ) {
			return;
		}

		$notification_id = $this->repo->create(
			array(
				'title'   => sprintf(
					__( 'Post published: "%s"', 'notification-hub' ),
					$post->post_title
				),
				'message' => sprintf(
					__( 'Status changed from %s to %s', 'notification-hub' ),
					$old_status,
					$new_status
				),
				'type'    => 'post_status',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'post_status' );
		}
	}
}
