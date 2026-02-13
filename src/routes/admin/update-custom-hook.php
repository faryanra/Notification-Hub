<?php
/**
 * Update Custom Hook Route
 *
 * admin_post handler for updating custom hooks.
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
 * Update_Custom_Hook Class
 */
class Update_Custom_Hook {

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
		add_action( 'admin_post_nh_update_hook', array( $this, 'handle' ) );
	}

	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function handle() {
		Security::ensure_cap();

		$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		Security::verify_nonce( 'nh_update_hook', $id );

		if ( $id <= 0 ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'nh_err' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$title    = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$action   = isset( $_POST['action_name'] ) ? Security::validate_action_name( wp_unslash( $_POST['action_name'] ) ) : '';
		$channels = isset( $_POST['channels'] ) ? Security::sanitize_channels( wp_unslash( $_POST['channels'] ) ) : array();

		if ( '' === $title || '' === $action ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'nh_err' => 1, 'edit' => $id ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Update.
		$result = $this->hooks_repo->update(
			$id,
			array(
				'title'       => $title,
				'action_name' => $action,
				'channels'    => $channels,
			)
		);

		if ( false === $result ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'nh_dup' => 1, 'edit' => $id ), admin_url( 'admin.php' ) ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'hook_updated' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
