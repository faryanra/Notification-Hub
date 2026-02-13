<?php
/**
 * Notification Factory
 *
 * Creates notification instances.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notification_Factory {

	public static function create( array $data ): array {
		return array(
			'source'     => isset( $data['source'] ) ? sanitize_key( $data['source'] ) : '',
			'type'       => isset( $data['type'] ) ? sanitize_key( $data['type'] ) : '',
			'title'      => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
			'message'    => isset( $data['message'] ) ? wp_kses_post( $data['message'] ) : '',
			'status'     => isset( $data['status'] ) ? (int) $data['status'] : 0,
			'priority'   => isset( $data['priority'] ) ? (int) $data['priority'] : 50,
			'tags'       => isset( $data['tags'] ) ? $data['tags'] : array(),
			'context'    => isset( $data['context'] ) ? $data['context'] : array(),
			'created_at' => current_time( 'mysql' ),
		);
	}

	public static function from_event( string $event_type, array $event_data ): array {
		$builder = new \Notification_Hub\Builders\Notification_Builder();

		$builder->source( $event_data['source'] ?? 'wordpress' )
				->type( $event_type )
				->title( $event_data['title'] ?? '' )
				->message( $event_data['message'] ?? '' )
				->context( $event_data['context'] ?? array() );

		if ( isset( $event_data['priority'] ) ) {
			$builder->priority( (int) $event_data['priority'] );
		}

		return $builder->build();
	}
}
