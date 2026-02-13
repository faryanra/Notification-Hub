<?php
/**
 * Post Status Changed Event
 *
 * Listens for post status transitions and creates notifications.
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
 * Post_Status_Changed Class
 */
class Post_Status_Changed implements Integration_Interface {

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
		add_action( 'transition_post_status', array( $this, 'on_status_change' ), 10, 3 );
	}

	/**
	 * Handle post status changes.
	 *
	 * @param string   $new  New status.
	 * @param string   $old  Old status.
	 * @param \WP_Post $post WP_Post object.
	 * @return void
	 */
	public function on_status_change( $new, $old, $post ) {
		if ( $new === $old ) {
			return;
		}

		// Skip WooCommerce orders.
		if ( ! is_object( $post ) ) {
			return;
		}

		$type = isset( $post->post_type ) ? $post->post_type : get_post_type( $post );
		if ( in_array( $type, array( 'shop_order', 'shop_order_placehold', 'shop_order_refund' ), true ) ) {
			return;
		}

		$title = sprintf(
			/* translators: 1: Post ID, 2: Old status, 3: New status. */
			esc_html__( 'Post %1$d status: %2$s → %3$s', 'notification-hub' ),
			(int) $post->ID,
			(string) $old,
			(string) $new
		);

		$message = esc_html( get_the_title( $post->ID ) );

		$context = array(
			'post_id' => (int) $post->ID,
			'old'     => (string) $old,
			'new'     => (string) $new,
		);

		// Insert notification.
		$notification_id = $this->notifications_repo->insert(
			array(
				'source'  => 'wp_core',
				'type'    => 'post_status_changed',
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
				'type'    => 'post_status_changed',
				'context' => $context,
				'link'    => function_exists( 'get_edit_post_link' ) ? (string) get_edit_post_link( (int) $post->ID, '' ) : '',
			);

			$this->dispatcher->dispatch( array( 'email', 'telegram', 'slack' ), $payload );
		}
	}
}
