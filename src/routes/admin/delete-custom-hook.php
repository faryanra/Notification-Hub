<?php
/**
 * Delete Custom Hook Route
 *
 * admin_post handler for deleting custom hooks.
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
 * Delete_Custom_Hook Class
 */
class Delete_Custom_Hook {

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
		add_action( 'admin_post_nh_delete_hook', array( $this, 'handle' ) );
	}

	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function handle() {
		Security::ensure_cap();

		$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		Security::verify_nonce( 'nh_delete_hook', $id );

		if ( $id <= 0 ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'nh_err' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$this->hooks_repo->delete( $id );

		wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'hook_deleted' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
