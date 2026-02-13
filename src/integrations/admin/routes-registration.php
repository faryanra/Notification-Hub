<?php
/**
 * Routes Registration
 *
 * Registers AJAX routes.
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
 * Routes Registration
 */
class Routes_Registration implements Integration_Interface {

	/**
	 * Create hook route.
	 *
	 * @var Create_Custom_Hook
	 */
	private $create_hook;

	/**
	 * Update hook route.
	 *
	 * @var Update_Custom_Hook
	 */
	private $update_hook;

	/**
	 * Delete hook route.
	 *
	 * @var Delete_Custom_Hook
	 */
	private $delete_hook;

	/**
	 * Test hook route.
	 *
	 * @var Test_Custom_Hook
	 */
	private $test_hook;

	/**
	 * Constructor.
	 *
	 * @param Create_Custom_Hook $create_hook Create route.
	 * @param Update_Custom_Hook $update_hook Update route.
	 * @param Delete_Custom_Hook $delete_hook Delete route.
	 * @param Test_Custom_Hook   $test_hook   Test route.
	 */
	public function __construct(
		Create_Custom_Hook $create_hook,
		Update_Custom_Hook $update_hook,
		Delete_Custom_Hook $delete_hook,
		Test_Custom_Hook $test_hook
	) {
		$this->create_hook = $create_hook;
		$this->update_hook = $update_hook;
		$this->delete_hook = $delete_hook;
		$this->test_hook   = $test_hook;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_nh_create_hook', array( $this->create_hook, 'handle' ) );
		add_action( 'wp_ajax_nh_update_hook', array( $this->update_hook, 'handle' ) );
		add_action( 'wp_ajax_nh_delete_hook', array( $this->delete_hook, 'handle' ) );
		add_action( 'wp_ajax_nh_test_hook', array( $this->test_hook, 'handle' ) );
	}
}
