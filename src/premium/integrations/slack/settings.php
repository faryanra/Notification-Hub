<?php
/**
 * Slack Settings Integration (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Integrations\Slack;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings implements Integration_Interface {

	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'nh_slack_settings', 'nh_slack_settings' );

		add_settings_section(
			'nh_slack_section',
			__( 'Slack Settings', 'notification-hub' ),
			null,
			'notification-hub-settings'
		);

		add_settings_field(
			'nh_slack_webhook_url',
			__( 'Webhook URL', 'notification-hub' ),
			array( $this, 'render_webhook_url_field' ),
			'notification-hub-settings',
			'nh_slack_section'
		);
	}

	public function render_webhook_url_field() {
		$settings = get_option( 'nh_slack_settings', array() );
		$value    = $settings['webhook_url'] ?? '';
		echo '<input type="url" name="nh_slack_settings[webhook_url]" value="' . esc_attr( $value ) . '" class="regular-text" />';
	}
}
