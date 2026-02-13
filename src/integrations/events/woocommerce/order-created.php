<?php
/**
 * WooCommerce Order Created Event
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WooCommerce;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Services\Notification_Dispatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Created
 */
class Order_Created implements Integration_Interface {

	private $repo;
	private $dispatcher;

	public function __construct( Notifications $repo, Notification_Dispatcher $dispatcher ) {
		$this->repo       = $repo;
		$this->dispatcher = $dispatcher;
	}

	public function register() {
		add_action( 'woocommerce_new_order', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$notification_id = $this->repo->create(
			array(
				'title'   => sprintf(
					__( 'New WooCommerce Order #%s', 'notification-hub' ),
					$order->get_order_number()
				),
				'message' => sprintf(
					__( 'Order total: %s', 'notification-hub' ),
					$order->get_formatted_order_total()
				),
				'type'    => 'woocommerce_order',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'woocommerce_order' );
		}
	}
}
