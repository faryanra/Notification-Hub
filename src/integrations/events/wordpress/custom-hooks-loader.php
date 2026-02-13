<?php
/**
 * Custom Hooks Loader
 *
 * Loads custom hooks from database and registers them.
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
 * Custom Hooks Loader
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
	 * @param Custom_Hooks             $hooks_repo         Custom hooks repository.
	 * @param Notifications            $notifications_repo Notifications repository.
	 * @param Notification_Dispatcher $dispatcher         Dispatcher.
	 */
	public function __construct( Custom_Hooks $hooks_repo, Notifications $notifications_repo, Notification_Dispatcher $dispatcher ) {
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
		add_action( 'init', array( $this, 'load_custom_hooks' ) );
	}

	/**
	 * Load custom hooks from database.
	 *
	 * @return void
	 */
	public function load_custom_hooks() {
		$hooks = $this->hooks_repo->get_all();

		foreach ( $hooks as $hook ) {
			if ( $hook->status !== 'active' ) {
				continue;
			}

			add_action(
				$hook->hook_name,
				function() use ( $hook ) {
					$this->handle_custom_hook( $hook );
				},
				10,
				0
			);
		}
	}

	/**
	 * Handle custom hook.
	 *
	 * @param object $hook Hook object.
	 * @return void
	 */
	private function handle_custom_hook( $hook ) {
		$notification_id = $this->notifications_repo->create(
			array(
				'title'   => $hook->title,
				'message' => $hook->message,
				'type'    => 'custom_hook',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'custom_hook' );
		}
	}
}
