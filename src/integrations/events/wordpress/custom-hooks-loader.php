<?php
/**
 * Custom Hooks Loader Integration
 *
 * Dynamically registers custom hooks from database.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\WordPress;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Custom_Hooks;
use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Services\Notification_Dispatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom_Hooks_Loader Class
 */
class Custom_Hooks_Loader implements Integration_Interface {

	/**
	 * Custom hooks repository.
	 *
	 * @var Custom_Hooks
	 */
	private $hooks_repo;

	/**
	 * Notifications repository.
	 *
	 * @var Notifications
	 */
	private $notifications_repo;

	/**
	 * Notification dispatcher.
	 *
	 * @var Notification_Dispatcher
	 */
	private $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param Custom_Hooks            $hooks_repo         Custom hooks repository.
	 * @param Notifications           $notifications_repo Notifications repository.
	 * @param Notification_Dispatcher $dispatcher         Notification dispatcher.
	 */
	public function __construct(
		Custom_Hooks $hooks_repo,
		Notifications $notifications_repo,
		Notification_Dispatcher $dispatcher
	) {
		$this->hooks_repo         = $hooks_repo;
		$this->notifications_repo = $notifications_repo;
		$this->dispatcher         = $dispatcher;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_custom_hooks' ) );
	}

	/**
	 * Register all active custom hooks dynamically.
	 *
	 * @return void
	 */
	public function register_custom_hooks() {
		$hooks = $this->hooks_repo->get_active();

		if ( empty( $hooks ) ) {
			return;
		}

		foreach ( $hooks as $hook ) {
			$action_name = trim( (string) $hook['action_name'] );
			if ( '' === $action_name ) {
				continue;
			}

			$channels = isset( $hook['channels'] ) ? json_decode( $hook['channels'], true ) : array();
			if ( ! is_array( $channels ) ) {
				$channels = array( 'email' );
			}

			add_action(
				$action_name,
				function ( $payload = array() ) use ( $hook, $action_name, $channels ) {
					$this->handle_custom_hook_fired( $hook, $action_name, $channels, $payload );
				},
				10,
				1
			);
		}
	}

	/**
	 * Handle custom hook fired.
	 *
	 * @param array  $hook        Hook data.
	 * @param string $action_name Action name.
	 * @param array  $channels    Channel slugs.
	 * @param mixed  $payload     Hook payload.
	 * @return void
	 */
	private function handle_custom_hook_fired( $hook, $action_name, $channels, $payload ) {
		if ( ! is_array( $payload ) ) {
			$payload = array();
		}

		$message = isset( $payload['message'] ) ? (string) $payload['message'] : '';
		if ( '' === $message ) {
			$message = sprintf(
				/* translators: %s: hook action name */
				esc_html__( 'Hook fired: %s', 'notification-hub' ),
				$action_name
			);
		}

		$source  = isset( $payload['source'] ) ? sanitize_text_field( (string) $payload['source'] ) : 'custom_hook';
		$title   = isset( $payload['title'] ) ? sanitize_text_field( (string) $payload['title'] ) : ( $hook['title'] ?: $action_name );
		$context = isset( $payload['context'] ) && is_array( $payload['context'] ) ? $payload['context'] : array();
		$context['hook_id'] = (int) $hook['id'];

		// Insert notification.
		$notification_id = $this->notifications_repo->insert(
			array(
				'source'  => $source,
				'type'    => 'custom_hook',
				'title'   => $title,
				'message' => $message,
				'context' => $context,
				'tags'    => array( 'custom_hook', $action_name ),
			)
		);

		// Dispatch to channels.
		if ( $notification_id && ! empty( $channels ) ) {
			$dispatch_payload = array(
				'title'   => $title,
				'summary' => $message,
				'source'  => $source,
				'type'    => 'custom_hook',
				'context' => $context,
			);

			$this->dispatcher->dispatch( $channels, $dispatch_payload );
		}
	}
}
