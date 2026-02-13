<?php
/**
 * Settings Registration Integration
 *
 * Registers plugin settings with WordPress Settings API.
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
 * Settings_Registration Class
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
		// General settings.
		register_setting( 'nh_settings', 'nh_retention_days' );
		register_setting( 'nh_settings', 'nh_email_to' );
		register_setting( 'nh_settings', 'nh_keep_data_on_uninstall' );

		// Premium settings.
		register_setting( 'nh_settings', 'nh_telegram_bot_token' );
		register_setting( 'nh_settings', 'nh_telegram_chat_id' );
		register_setting( 'nh_settings', 'nh_slack_webhook' );
	}
}
