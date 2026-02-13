<?php
/**
 * Routes Registration Integration
 *
 * Registers admin_post route handlers.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Routes\Admin\Create_Custom_Hook;
use Notification_Hub\Routes\Admin\Update_Custom_Hook;
use Notification_Hub\Routes\Admin\Delete_Custom_Hook;
use Notification_Hub\Routes\Admin\Test_Custom_Hook;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes_Registration Class
 */
class Routes_Registration implements Integration_Interface {

	/**
	 * Route handlers.
	 *
	 * @var array
	 */
	private $routes;

	/**
	 * Constructor.
	 *
	 * @param Create_Custom_Hook $create_hook Create hook handler.
	 * @param Update_Custom_Hook $update_hook Update hook handler.
	 * @param Delete_Custom_Hook $delete_hook Delete hook handler.
	 * @param Test_Custom_Hook   $test_hook   Test hook handler.
	 */
	public function __construct(
		Create_Custom_Hook $create_hook,
		Update_Custom_Hook $update_hook,
		Delete_Custom_Hook $delete_hook,
		Test_Custom_Hook $test_hook
	) {
		$this->routes = array(
			$create_hook,
			$update_hook,
			$delete_hook,
			$test_hook,
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		foreach ( $this->routes as $route ) {
			$route->register();
		}
	}
}
