<?php
/**
 * Queue Processor Service
 *
 * (Extracted from NH_Notifier_Queue)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Queue_Processor {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function queue_send( string $channel, array $payload ): bool {
		$channel = sanitize_key( $channel );

		if ( ! empty( $payload['no_log'] ) ) {
			$payload['notification_id'] = 0;
		} else {
			$payload['notification_id'] = $this->log_to_database( $payload );
		}

		// TODO: Implement actual queue (wp_schedule_single_event or ActionScheduler)
		// For now: send immediately
		$notifier = $this->container->get_svc( 'notifier' );
		if ( $notifier ) {
			return $notifier->send_now( $channel, $payload );
		}

		return false;
	}

	private function log_to_database( array $payload ): int {
		$db = $this->container->get_svc( 'db' );

		if ( ! $db || ! method_exists( $db, 'insert_notification' ) ) {
			return 0;
		}

		$data = $this->normalize_payload( $payload );

		return (int) $db->insert_notification(
			array(
				'source'   => $data['source'],
				'type'     => $data['type'],
				'title'    => $data['title'],
				'message'  => $data['message'],
				'status'   => 0,
				'context'  => $data['context'],
				'priority' => $data['priority'],
				'tags'     => $data['tags'],
			)
		);
	}

	private function normalize_payload( array $payload ): array {
		$context = array();
		if ( ! empty( $payload['context'] ) && is_array( $payload['context'] ) ) {
			$context = $payload['context'];
		}

		$source = isset( $payload['source'] ) ? sanitize_key( (string) $payload['source'] ) : '';
		$type   = isset( $payload['type'] ) ? sanitize_key( (string) $payload['type'] ) : '';

		$title = '';
		if ( ! empty( $payload['title'] ) && is_string( $payload['title'] ) ) {
			$title = $payload['title'];
		}

		$message = '';
		if ( ! empty( $payload['body'] ) && is_string( $payload['body'] ) ) {
			$message = $payload['body'];
		} elseif ( ! empty( $payload['message'] ) && is_string( $payload['message'] ) ) {
			$message = $payload['message'];
		}

		if ( $type === '' ) {
			if ( ! empty( $payload['event_type'] ) ) {
				$type = sanitize_key( (string) $payload['event_type'] );
			} elseif ( ! empty( $context['type'] ) ) {
				$type = sanitize_key( (string) $context['type'] );
			}
		}

		if ( $source === '' && ! empty( $context['source'] ) ) {
			$source = sanitize_key( (string) $context['source'] );
		}

		$priority_calc = new Priority_Calculator();
		$priority      = $priority_calc->calculate( $source, $type, $payload['priority'] ?? null );

		$tags = $this->normalize_tags( $payload['tags'] ?? null, $source, $type );

		return array(
			'source'   => $source,
			'type'     => $type,
			'title'    => $title,
			'message'  => $message,
			'context'  => ! empty( $context ) ? wp_json_encode( $context ) : null,
			'priority' => $priority,
			'tags'     => $tags,
		);
	}

	private function normalize_tags( $tags, string $source, string $type ): ?string {
		$tags_arr = array();

		if ( ! empty( $tags ) ) {
			if ( is_string( $tags ) ) {
				$decoded  = json_decode( $tags, true );
				$tags_arr = is_array( $decoded ) ? $decoded : array( $tags );
			} elseif ( is_array( $tags ) ) {
				$tags_arr = $tags;
			} else {
				$tags_arr = array( (string) $tags );
			}
		} else {
			$tags_arr = array_filter( array( $source, $type ) );
		}

		$tags_arr = array_values( array_unique( array_map( 'strval', $tags_arr ) ) );

		return ! empty( $tags_arr ) ? wp_json_encode( $tags_arr ) : null;
	}

	public function log_delivery_status( int $notif_id, string $channel, bool $success, string $error = '' ): void {
		if ( ! $notif_id ) {
			return;
		}

		$db = $this->container->get_svc( 'db' );

		if ( ! $db || ! method_exists( $db, 'log_delivery_status' ) ) {
			return;
		}

		$db->log_delivery_status(
			$notif_id,
			array(
				'status'     => $success ? 'sent' : 'error',
				'error_msg'  => $error,
				'channel'    => sanitize_key( $channel ),
				'updated_at' => current_time( 'mysql' ),
			)
		);
	}
}
