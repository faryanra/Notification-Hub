<?php
/**
 * Queue Processor Service
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

use Notification_Hub\Repositories\Queue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queue Processor
 */
class Queue_Processor {

	private $queue;

	public function __construct( Queue $queue ) {
		$this->queue = $queue;
	}

	public function process( $limit = 10 ) {
		$items = $this->queue->get_pending( $limit );

		foreach ( $items as $item ) {
			do_action( 'nh_process_queue_item', $item );

			$this->queue->update_status( $item->id, 'processed' );
		}
	}
}
