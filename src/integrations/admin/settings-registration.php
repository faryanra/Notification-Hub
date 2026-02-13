<?php
/**
 * Settings Registration
 *
 * Registers plugin settings.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Registration
 */
class Settings_Registration implements Integration_Interface {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'notification_hub_settings', 'nh_email_enabled' );
		register_setting( 'notification_hub_settings', 'nh_admin_email' );
		register_setting( 'notification_hub_settings', 'nh_retention_days' );
	}
}
