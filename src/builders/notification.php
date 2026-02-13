<?php
/**
 * Notification Builder
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Builders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Builder
 */
class Notification {

	private $data = array();

	public function set_title( $title ) {
		$this->data['title'] = $title;
		return $this;
	}

	public function set_message( $message ) {
		$this->data['message'] = $message;
		return $this;
	}

	public function set_type( $type ) {
		$this->data['type'] = $type;
		return $this;
	}

	public function set_status( $status ) {
		$this->data['status'] = $status;
		return $this;
	}

	public function build() {
		return $this->data;
	}
}
