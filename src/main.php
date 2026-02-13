<?php
/**
 * Notification Hub Main Container
 *
 * Dependency Injection Container inspired by Yoast SEO architecture.
 * Manages service registration and retrieval.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Container Class
 */
class Main {

	/**
	 * Container instance.
	 *
	 * @var array
	 */
	private $services = array();

	/**
	 * Singleton instance.
	 *
	 * @var Main|null
	 */
	private static $instance = null;

	/**
	 * Get container instance.
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		// Services will be registered by Loader.
	}

	/**
	 * Register a service.
	 *
	 * @param string $key Service key.
	 * @param mixed  $service Service instance or callable.
	 * @return void
	 */
	public function set( $key, $service ) {
		$this->services[ $key ] = $service;
	}

	/**
	 * Get a service.
	 *
	 * @param string $key Service key.
	 * @return mixed|null
	 */
	public function get( $key ) {
		if ( ! isset( $this->services[ $key ] ) ) {
			return null;
		}

		$service = $this->services[ $key ];

		// If callable, resolve it once and cache.
		if ( is_callable( $service ) && ! is_object( $service ) ) {
			$this->services[ $key ] = call_user_func( $service );
			return $this->services[ $key ];
		}

		return $service;
	}

	/**
	 * Check if a service exists.
	 *
	 * @param string $key Service key.
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->services[ $key ] );
	}

	/**
	 * Get all registered services (for debugging).
	 *
	 * @return array
	 */
	public function get_all() {
		return array_keys( $this->services );
	}
}
