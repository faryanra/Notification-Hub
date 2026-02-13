<?php
/**
 * Test Custom Hook Route
 *
 * admin_post handler for testing custom hooks.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

use Notification_Hub\Helpers\Security;
use Notification_Hub\Repositories\Custom_Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test_Custom_Hook Class
 */
class Test_Custom_Hook {

	/**
	 * Custom hooks repository.
	 *
	 * @var Custom_Hooks
	 */
	private $hooks_repo;

	/**
	 * Constructor.
	 *
	 * @param Custom_Hooks $hooks_repo Custom hooks repository.
	 */
	public function __construct( Custom_Hooks $hooks_repo ) {
		$this->hooks_repo = $hooks_repo;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_post_nh_test_hook', array( $this, 'handle' ) );
	}

	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function handle() {
		Security::ensure_cap();

		$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		Security::verify_nonce( 'nh_test_hook', $id );

		$hook = $this->hooks_repo->get_by_id( $id );

		if ( ! $hook ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'notfound' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Fire the custom hook.
		do_action(
			(string) $hook['action_name'],
			array(
				'test'    => true,
				'source'  => 'custom_hook_test',
				'title'   => sprintf(
					/* translators: %s: hook action name */
					esc_html__( 'Test: %s', 'notification-hub' ),
					$hook['title']
				),
				'message' => sprintf(
					/* translators: %s: hook action name */
					esc_html__( 'This is a test notification for hook: %s', 'notification-hub' ),
					(string) $hook['action_name']
				),
				'context' => array( 'hook_id' => (int) $hook['id'] ),
			)
		);

		wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'hook_tested' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
