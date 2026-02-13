<?php
/**
 * Network Policy Service (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Network_Policy {

	public function is_network_activated() {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins', array() );

		return isset( $plugins[ plugin_basename( NH_PLUGIN_FILE ) ] );
	}

	public function can_manage_network_settings() {
		return is_multisite() && current_user_can( 'manage_network_options' );
	}
}
