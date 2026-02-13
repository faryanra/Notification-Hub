<?php
/**
 * Save Hook Route
 *
 * AJAX handler for saving custom hooks.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_Hook {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		add_action( 'wp_ajax_nh_save_hook', array( $this, 'handle' ) );
	}

	public function handle() {
		check_ajax_referer( 'nh_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$id          = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$action_name = isset( $_POST['action_name'] ) ? sanitize_text_field( $_POST['action_name'] ) : '';
		$channels    = isset( $_POST['channels'] ) && is_array( $_POST['channels'] ) ? $_POST['channels'] : array();
		$status      = isset( $_POST['status'] ) ? (int) $_POST['status'] : 1;

		if ( empty( $title ) || empty( $action_name ) ) {
			wp_send_json_error( array( 'message' => 'Title and action name required' ), 400 );
		}

		$repo = new \Notification_Hub\Repositories\Custom_Hooks();

		$data = array(
			'title'       => $title,
			'action_name' => $action_name,
			'channels'    => $channels,
			'status'      => $status,
		);

		if ( $id ) {
			$repo->update( $id, $data );
		} else {
			$id = $repo->insert( $data );
		}

		wp_send_json_success( array( 'id' => $id ) );
	}
}
