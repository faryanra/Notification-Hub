<?php
/**
 * Low Stock Alert Event
 *
 * (Extracted from NH_Int_WooCommerce::on_low_stock)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WooCommerce;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Low_Stock_Alert implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'woocommerce_low_stock', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $product ) {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_name' ) ) {
			return;
		}

		$qty = method_exists( $product, 'get_stock_quantity' ) ? $product->get_stock_quantity() : 0;

		$title = sprintf(
			esc_html__( 'Low stock: %s', 'notification-hub' ),
			(string) $product->get_name()
		);

		$message = sprintf(
			esc_html__( 'Stock: %d', 'notification-hub' ),
			(int) $qty
		);

		$product_id = method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;

		$e = array(
			'source'  => 'woocommerce',
			'type'    => 'low_stock',
			'title'   => $title,
			'message' => $message,
			'context' => array(
				'product_id' => $product_id,
				'stock'      => (int) $qty,
			),
		);

		$db = $this->container->get_svc( 'db' );
		if ( $db && method_exists( $db, 'insert_notification' ) ) {
			$db->insert_notification( $e );
		}

		$this->fanout_send( $e, $product_id );
	}

	private function fanout_send( array $e, int $product_id ) {
		$notifier = $this->container->get_svc( 'notifier' );
		if ( ! $notifier ) {
			return;
		}

		$link = '';
		if ( $product_id && function_exists( 'get_edit_post_link' ) ) {
			$link = (string) get_edit_post_link( $product_id, '' );
		}

		$payload = array(
			'title'   => $e['title'] ?? '',
			'summary' => $e['message'] ?? '',
			'source'  => 'woocommerce',
			'type'    => 'low_stock',
			'context' => $e['context'] ?? array(),
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
