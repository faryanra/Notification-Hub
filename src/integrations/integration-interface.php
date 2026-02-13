<?php
/**
 * Integration Interface
 *
 * All integrations must implement this interface.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration Interface
 */
interface Integration_Interface {

	/**
	 * Register hooks for this integration.
	 *
	 * @return void
	 */
	public function register();
}
