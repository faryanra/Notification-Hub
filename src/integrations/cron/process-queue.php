<?php
/**
 * Process Queue Cron
 *
 * Processes pending queue items.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Cron;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Process_Queue implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'nh_process_queue', array( $this, 'handle' ) );
	}

	public function handle() {
		$repo = new \Notification_Hub\Repositories\Queue();
		$items = $repo->get_pending( 10 );

		if ( empty( $items ) ) {
			return;
		}

		$notifier = $this->container->get_svc( 'notifier' );

		if ( ! $notifier ) {
			return;
		}

		foreach ( $items as $item ) {
			$channel = isset( $item['channel'] ) ? sanitize_key( $item['channel'] ) : '';
			$payload = isset( $item['payload'] ) ? json_decode( $item['payload'], true ) : array();

			if ( ! $channel || ! is_array( $payload ) ) {
				continue;
			}

			$success = $notifier->send_now( $channel, $payload );

			if ( $success ) {
				$repo->mark_processed( (int) $item['id'] );
			}
		}
	}
}
