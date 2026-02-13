<?php
/**
 * Comment Posted Event
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
 * Comment Posted
 */
class Comment_Posted implements Integration_Interface {

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
		add_action( 'wp_insert_comment', array( $this, 'handle' ), 10, 2 );
	}

	/**
	 * Handle comment posted.
	 *
	 * @param int    $comment_id Comment ID.
	 * @param object $comment    Comment object.
	 * @return void
	 */
	public function handle( $comment_id, $comment ) {
		if ( $comment->comment_approved !== '1' ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );

		if ( ! $post ) {
			return;
		}

		$notification_id = $this->repo->create(
			array(
				'title'   => sprintf(
					__( 'New comment on "%s"', 'notification-hub' ),
					$post->post_title
				),
				'message' => wp_trim_words( $comment->comment_content, 20 ),
				'type'    => 'comment',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'comment' );
		}
	}
}
