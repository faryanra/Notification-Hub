<?php
/**
 * Email Builder
 *
 * Fluent builder for email payloads.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Builders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Builder {

	private $data = array();

	public function to( string $email ): self {
		$this->data['to'] = sanitize_email( $email );
		return $this;
	}

	public function subject( string $subject ): self {
		$this->data['subject'] = sanitize_text_field( $subject );
		return $this;
	}

	public function body( string $body ): self {
		$this->data['body'] = $body;
		return $this;
	}

	public function html( bool $is_html = true ): self {
		$this->data['html'] = $is_html;
		return $this;
	}

	public function from( string $email, string $name = '' ): self {
		$this->data['from_email'] = sanitize_email( $email );
		if ( $name ) {
			$this->data['from_name'] = sanitize_text_field( $name );
		}
		return $this;
	}

	public function reply_to( string $email ): self {
		$this->data['reply_to'] = sanitize_email( $email );
		return $this;
	}

	public function build(): array {
		return $this->data;
	}
}
