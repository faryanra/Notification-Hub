<?php
/**
 * Custom Hooks Loader
 *
 * Loads custom hooks from DB and registers them.
 * (Extracted from NH_Int_WP_Core::register_custom_hooks)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Custom_Hooks_Loader implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'nh_hooks';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT id, action_name, title, channels FROM {$table}" );
		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			$hook_name = trim( (string) ( $row->action_name ?? '' ) );
			if ( $hook_name === '' ) {
				continue;
			}

			$channels = $this->decode_channels( $row->channels ?? '' );
			$primary  = $channels[0] ?? 'email';

			add_action(
				$hook_name,
				function ( ...$args ) use ( $row, $hook_name, $primary ) {
					$payload = $this->normalize_payload( $row, $hook_name, $args );

					$event = array(
						'source'  => 'hook',
						'type'    => $hook_name,
						'title'   => $payload['title'] ?? ( $row->title ?: sprintf( esc_html__( 'Hook: %s', 'notification-hub' ), $hook_name ) ),
						'message' => $payload['body'] ?? '',
						'context' => array( 'hook' => $hook_name ),
					);

					$db = $this->container->get_svc( 'db' );
					if ( $db && method_exists( $db, 'insert_notification' ) ) {
						$db->insert_notification( $event );
					}

					$notifier = $this->container->get_svc( 'notifier' );
					if ( $notifier && method_exists( $notifier, 'queue_send' ) ) {
						$notifier->queue_send(
							$primary,
							array(
								'title'   => $event['title'],
								'summary' => $event['message'],
								'source'  => $event['source'],
								'type'    => $event['type'],
								'context' => $event['context'],
								'no_log'  => true,
							)
						);
					}
				},
				10,
				10
			);
		}
	}

	private function decode_channels( $json ): array {
		if ( ! is_string( $json ) || $json === '' ) {
			return array( 'email' );
		}

		$arr = json_decode( $json, true );
		if ( ! is_array( $arr ) || empty( $arr ) ) {
			return array( 'email' );
		}

		return array_values(
			array_filter(
				array_map(
					static function ( $c ) {
						$c = strtolower( trim( (string) $c ) );
						return in_array( $c, array( 'email', 'telegram', 'slack' ), true ) ? $c : null;
					},
					$arr
				)
			)
		);
	}

	private function normalize_payload( $row, $hook_name, array $args ): array {
		if ( ! empty( $args ) && is_array( $args[0] ) ) {
			$p = $args[0];

			$fallback_title = $row->title ?: sprintf( esc_html__( 'Hook: %s', 'notification-hub' ), $hook_name );

			return array(
				'title'  => isset( $p['title'] ) ? sanitize_text_field( (string) $p['title'] ) : $fallback_title,
				'body'   => isset( $p['body'] ) ? sanitize_textarea_field( (string) $p['body'] ) : '',
				'source' => isset( $p['source'] ) ? sanitize_text_field( (string) $p['source'] ) : 'hook',
			);
		}

		$summary = '';
		if ( ! empty( $args ) ) {
			$summary = wp_json_encode( array_slice( $args, 0, 3 ) );
		}

		return array(
			'title'  => $row->title ?: sprintf( esc_html__( 'Hook: %s', 'notification-hub' ), $hook_name ),
			'body'   => $summary,
			'source' => 'hook',
		);
	}
}
