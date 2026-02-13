<?php
/**
 * Create Custom Hook Route
 *
 * admin_post handler for creating custom hooks.
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
 * Create_Custom_Hook Class
 */
class Create_Custom_Hook {

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
		add_action( 'admin_post_nh_save_hook', array( $this, 'handle' ) );
	}

	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public function handle() {
		Security::ensure_cap();
		Security::verify_nonce( 'nh_save_hook' );

		$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$action  = isset( $_POST['action_name'] ) ? Security::validate_action_name( wp_unslash( $_POST['action_name'] ) ) : '';
		$channels = isset( $_POST['channels'] ) ? Security::sanitize_channels( wp_unslash( $_POST['channels'] ) ) : array();

		if ( '' === $title || '' === $action ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'nh_err' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Check duplicate.
		if ( $this->hooks_repo->exists_by_action( $action ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'nh_dup' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$this->hooks_repo->insert(
			array(
				'title'       => $title,
				'action_name' => $action,
				'channels'    => $channels,
				'status'      => 1,
			)
		);

		wp_safe_redirect( add_query_arg( array( 'page' => 'nh-hooks', 'hook_saved' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
