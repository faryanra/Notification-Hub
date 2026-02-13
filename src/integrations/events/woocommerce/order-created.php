<?php
/**
 * Order Created Event
 *
 * (Extracted from NH_Int_WooCommerce::on_new_order)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WooCommerce;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Created implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'woocommerce_new_order', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $order_id ) {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$total = (float) $order->get_total();

		$title = sprintf(
			esc_html__( 'New Order #%d', 'notification-hub' ),
			(int) $order_id
		);

		$message = sprintf(
			esc_html__( 'Total: %s', 'notification-hub' ),
			wc_price( $total )
		);

		$e = array(
			'source'  => 'woocommerce',
			'type'    => 'order_created',
			'title'   => $title,
			'message' => $message,
			'context' => array(
				'order_id' => (int) $order_id,
				'total'    => $total,
				'currency' => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
			),
		);

		$db = $this->container->get_svc( 'db' );
		if ( $db && method_exists( $db, 'insert_notification' ) ) {
			$db->insert_notification( $e );
		}

		$this->fanout_send( $e );
	}

	private function fanout_send( array $e ) {
		$notifier = $this->container->get_svc( 'notifier' );
		if ( ! $notifier ) {
			return;
		}

		$context = isset( $e['context'] ) && is_array( $e['context'] ) ? $e['context'] : array();

		$link = '';
		if ( ! empty( $context['order_id'] ) && function_exists( 'get_edit_post_link' ) ) {
			$link = (string) get_edit_post_link( (int) $context['order_id'], '' );
		}

		$payload = array(
			'title'   => $e['title'] ?? '',
			'summary' => $e['message'] ?? '',
			'source'  => 'woocommerce',
			'type'    => 'order_created',
			'context' => $context,
			'link'    => $link,
			'no_log'  => true,
		);

		if ( method_exists( $notifier, 'queue_send' ) ) {
			$notifier->queue_send( 'email', $payload );
			$notifier->queue_send( 'telegram', $payload );
			$notifier->queue_send( 'slack', $payload );
		}
	}
}
