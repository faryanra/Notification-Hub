<?php
/**
 * Comment Posted Event
 *
 * (Extracted from NH_Int_WP_Core::on_comment)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Comment_Posted implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'wp_insert_comment', array( $this, 'handle' ), 10, 2 );
	}

	public function handle( $id, $comment ) {
		if ( isset( $comment->comment_type ) && $comment->comment_type === 'order_note' ) {
			return;
		}

		$post_type = get_post_type( $comment->comment_post_ID );
		if ( $post_type === 'shop_order' ) {
			return;
		}

		$title = sprintf(
			esc_html__( 'New comment by %s', 'notification-hub' ),
			(string) $comment->comment_author
		);
		$body = wp_kses_post( wp_trim_words( (string) $comment->comment_content, 20 ) );

		$context = array(
			'comment_id' => (int) $id,
			'post_id'    => (int) $comment->comment_post_ID,
			'actor'      => (string) $comment->comment_author,
		);

		$db = $this->container->get_svc( 'db' );
		if ( $db && method_exists( $db, 'insert_notification' ) ) {
			$db->insert_notification(
				array(
					'source'  => 'wp_core',
					'type'    => 'comment_new',
					'title'   => $title,
					'message' => $body,
					'context' => $context,
				)
			);
		}

		$notifier = $this->container->get_svc( 'notifier' );
		if ( $notifier && method_exists( $notifier, 'queue_send' ) ) {
			$payload = array(
				'title'   => $title,
				'summary' => $body,
				'source'  => 'wp_core',
				'type'    => 'comment_new',
				'context' => $context,
				'link'    => function_exists( 'get_edit_comment_link' ) ? (string) get_edit_comment_link( (int) $id ) : '',
				'no_log'  => true,
			);

			$notifier->queue_send( 'email', $payload );
			$notifier->queue_send( 'telegram', $payload );
			$notifier->queue_send( 'slack', $payload );
		}
	}
}
