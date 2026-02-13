<?php
/**
 * Notification Builder
 *
 * Fluent builder for creating notifications.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Builders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notification_Builder {

	private $data = array();

	public function source( string $source ): self {
		$this->data['source'] = sanitize_key( $source );
		return $this;
	}

	public function type( string $type ): self {
		$this->data['type'] = sanitize_key( $type );
		return $this;
	}

	public function title( string $title ): self {
		$this->data['title'] = sanitize_text_field( $title );
		return $this;
	}

	public function message( string $message ): self {
		$this->data['message'] = $message;
		return $this;
	}

	public function priority( int $priority ): self {
		$this->data['priority'] = max( 0, min( 100, $priority ) );
		return $this;
	}

	public function context( array $context ): self {
		$this->data['context'] = $context;
		return $this;
	}

	public function tags( array $tags ): self {
		$this->data['tags'] = $tags;
		return $this;
	}

	public function build(): array {
		return $this->data;
	}
}
