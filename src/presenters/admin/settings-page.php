<?php
/**
 * Settings Page Presenter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Page Presenter
 */
class Settings_Page {

	/**
	 * Render page.
	 *
	 * @return void
	 */
	public function render() {
		include NH_PLUGIN_DIR . 'templates/admin/settings.php';
	}
}
