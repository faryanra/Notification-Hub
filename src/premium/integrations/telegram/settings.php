<?php
/**
 * Telegram Settings Integration (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Integrations\Telegram;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings implements Integration_Interface {

	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'nh_telegram_settings', 'nh_telegram_settings' );

		add_settings_section(
			'nh_telegram_section',
			__( 'Telegram Settings', 'notification-hub' ),
			null,
			'notification-hub-settings'
		);

		add_settings_field(
			'nh_telegram_bot_token',
			__( 'Bot Token', 'notification-hub' ),
			array( $this, 'render_bot_token_field' ),
			'notification-hub-settings',
			'nh_telegram_section'
		);

		add_settings_field(
			'nh_telegram_chat_id',
			__( 'Chat ID', 'notification-hub' ),
			array( $this, 'render_chat_id_field' ),
			'notification-hub-settings',
			'nh_telegram_section'
		);
	}

	public function render_bot_token_field() {
		$settings = get_option( 'nh_telegram_settings', array() );
		$value    = $settings['bot_token'] ?? '';
		echo '<input type="text" name="nh_telegram_settings[bot_token]" value="' . esc_attr( $value ) . '" class="regular-text" />';
	}

	public function render_chat_id_field() {
		$settings = get_option( 'nh_telegram_settings', array() );
		$value    = $settings['chat_id'] ?? '';
		echo '<input type="text" name="nh_telegram_settings[chat_id]" value="' . esc_attr( $value ) . '" class="regular-text" />';
	}
}
