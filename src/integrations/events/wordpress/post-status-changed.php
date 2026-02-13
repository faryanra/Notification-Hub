<?php
/**
 * Post Status Changed Event
 *
 * (Extracted from NH_Int_WP_Core::on_post_status)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Status_Changed implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'transition_post_status', array( $this, 'handle' ), 10, 3 );
	}

	public function handle( $new, $old, $post ) {
		if ( $new === $old ) {
			return;
		}

		if ( ! is_object( $post ) ) {
			return;
		}

		$type = isset( $post->post_type ) ? $post->post_type : get_post_type( $post );
		if ( in_array( $type, array( 'shop_order', 'shop_order_placehold', 'shop_order_refund' ), true ) ) {
			return;
		}

		$title = sprintf(
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

		$e = array(
			'source'  => 'wp_core',
			'type'    => 'post_status_changed',
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
				'link'    => function_exists( 'get_edit_post_link' ) ? (string) get_edit_post_link( (int) $post->ID, '' ) : '',
				'no_log'  => true,
			);

			$notifier->queue_send( 'email', $payload );
			$notifier->queue_send( 'telegram', $payload );
			$notifier->queue_send( 'slack', $payload );
		}
	}
}
