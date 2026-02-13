<?php
/**
 * Payload Builder
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Builders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payload Builder
 */
class Payload {

	private $data = array();

	public function set_channel( $channel ) {
		$this->data['channel'] = $channel;
		return $this;
	}

	public function set_recipient( $recipient ) {
		$this->data['recipient'] = $recipient;
		return $this;
	}

	public function set_content( $content ) {
		$this->data['content'] = $content;
		return $this;
	}

	public function build() {
		return $this->data;
	}
}
