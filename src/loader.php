<?php
/**
 * Loader (Hook Manager)
 *
 * Manages hooks and integrations.
 * (Refactored from NH_Loader)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loader {

	private $container;

	public function __construct( Main $container ) {
		$this->container = $container;
	}

	public function boot() {
		// TODO: Register integrations here
		// For now, keep existing boot logic from NH_Loader
	}
}
