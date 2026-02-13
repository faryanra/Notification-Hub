<?php
/**
 * License Presenter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\License\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License_Presenter {

	public static function render_license_box() {
		$license_key = get_option( 'nh_license_key', '' );
		$status      = get_option( 'nh_license_status', 'inactive' );

		include NH_PLUGIN_DIR . 'templates/settings/partials/premium/license-box.php';
	}

	public static function render_debug_panel() {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		include NH_PLUGIN_DIR . 'templates/settings/partials/premium/license-debug-panel.php';
	}
}
