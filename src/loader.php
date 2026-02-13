<?php
/**
 * Notification Hub Loader
 *
 * Hook Manager inspired by Yoast SEO architecture.
 * Manages integration loading based on conditionals.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader Class
 */
class Loader {

	/**
	 * Container instance.
	 *
	 * @var Main
	 */
	private $container;

	/**
	 * Registered integrations.
	 *
	 * @var array
	 */
	private $integrations = array();

	/**
	 * Constructor.
	 *
	 * @param Main $container Container instance.
	 */
	public function __construct( Main $container ) {
		$this->container = $container;
	}

	/**
	 * Register an integration.
	 *
	 * @param Integration_Interface $integration Integration instance.
	 * @param array                 $conditionals Conditional classes.
	 * @return void
	 */
	public function register( Integration_Interface $integration, array $conditionals = array() ) {
		// Check if all conditionals are met.
		foreach ( $conditionals as $conditional ) {
			if ( ! $conditional->is_met() ) {
				return; // Skip this integration.
			}
		}

		$this->integrations[] = $integration;
	}

	/**
	 * Initialize all registered integrations.
	 *
	 * @return void
	 */
	public function initialize() {
		foreach ( $this->integrations as $integration ) {
			$integration->register();
		}
	}

	/**
	 * Get container.
	 *
	 * @return Main
	 */
	public function get_container() {
		return $this->container;
	}

	/**
	 * Get all registered integrations (for debugging).
	 *
	 * @return array
	 */
	public function get_integrations() {
		return $this->integrations;
	}
}
