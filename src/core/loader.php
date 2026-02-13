<?php
/**
 * Loader
 *
 * Registers all hooks and integrations.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loader {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		// Register all integrations from bootstrap
		$this->container->get_svc( 'bootstrap' )->register_integrations();
	}
}
