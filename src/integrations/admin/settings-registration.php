<?php
/**
 * Settings Registration Integration
 *
 * Registers WordPress settings.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Registration implements Integration_Interface {

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'nh_settings', 'nh_email_recipients' );
		register_setting( 'nh_settings', 'nh_enabled_channels' );
		register_setting( 'nh_settings', 'nh_retention_days' );
	}
}
