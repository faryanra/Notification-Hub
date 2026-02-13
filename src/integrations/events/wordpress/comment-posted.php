<?php
/**
 * Comment Posted Event
 *
 * Listens for new comment submissions and creates notifications.
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
 * Comment_Posted Class
 */
class Comment_Posted implements Integration_Interface {

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
		add_action( 'wp_insert_comment', array( $this, 'on_comment' ), 10, 2 );
	}

	/**
	 * Handle new comment notifications.
	 *
	 * @param int    $id      Comment ID.
	 * @param object $comment WP_Comment.
	 * @return void
	 */
	public function on_comment( $id, $comment ) {
		// Skip WooCommerce order notes.
		if ( isset( $comment->comment_type ) && 'order_note' === $comment->comment_type ) {
			return;
		}

		// Skip if comment belongs to WooCommerce order post.
		$post_type = get_post_type( $comment->comment_post_ID );
		if ( 'shop_order' === $post_type ) {
			return;
		}

		$title = sprintf(
			/* translators: %s: Comment author name. */
			esc_html__( 'New comment by %s', 'notification-hub' ),
			(string) $comment->comment_author
		);

		$body = wp_kses_post( wp_trim_words( (string) $comment->comment_content, 20 ) );

		$context = array(
			'comment_id' => (int) $id,
			'post_id'    => (int) $comment->comment_post_ID,
			'actor'      => (string) $comment->comment_author,
		);

		// Insert notification.
		$notification_id = $this->notifications_repo->insert(
			array(
				'source'  => 'wp_core',
				'type'    => 'comment_new',
				'title'   => $title,
				'message' => $body,
				'context' => $context,
			)
		);

		// Dispatch to channels.
		if ( $notification_id ) {
			$payload = array(
				'title'   => $title,
				'summary' => $body,
				'source'  => 'wp_core',
				'type'    => 'comment_new',
				'context' => $context,
				'link'    => function_exists( 'get_edit_comment_link' ) ? (string) get_edit_comment_link( (int) $id ) : '',
			);

			$this->dispatcher->dispatch( array( 'email', 'telegram', 'slack' ), $payload );
		}
	}
}
