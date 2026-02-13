<?php
/**
 * Multisite Network Settings Integration (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Multisite_Network_Settings implements Integration_Interface {

	public function register() {
		add_action( 'network_admin_menu', array( $this, 'add_network_menu' ) );
	}

	public function add_network_menu() {
		add_menu_page(
			__( 'Notification Hub Network', 'notification-hub' ),
			__( 'NH Network', 'notification-hub' ),
			'manage_network_options',
			'notification-hub-network',
			array( $this, 'render_network_page' ),
			'dashicons-bell',
			30
		);
	}

	public function render_network_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Notification Hub - Network Settings', 'notification-hub' ); ?></h1>
			<p><?php esc_html_e( 'Configure network-wide notification settings.', 'notification-hub' ); ?></p>
			<!-- TODO: Add network settings form -->
		</div>
		<?php
	}
}
