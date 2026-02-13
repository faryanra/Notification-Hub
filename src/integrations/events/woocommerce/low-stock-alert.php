<?php
/**
 * WooCommerce Low Stock Alert Event
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
 * Low Stock Alert
 */
class Low_Stock_Alert implements Integration_Interface {

	private $repo;
	private $dispatcher;

	public function __construct( Notifications $repo, Notification_Dispatcher $dispatcher ) {
		$this->repo       = $repo;
		$this->dispatcher = $dispatcher;
	}

	public function register() {
		add_action( 'woocommerce_low_stock', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $product ) {
		if ( ! $product ) {
			return;
		}

		$notification_id = $this->repo->create(
			array(
				'title'   => __( 'Low Stock Alert', 'notification-hub' ),
				'message' => sprintf(
					__( 'Product "%s" is running low on stock (%d remaining)', 'notification-hub' ),
					$product->get_name(),
					$product->get_stock_quantity()
				),
				'type'    => 'woocommerce_stock',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'woocommerce_stock' );
		}
	}
}
