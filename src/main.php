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

use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Repositories\Custom_Hooks;
use Notification_Hub\Services\Notification_Dispatcher;
use Notification_Hub\Presenters\Admin\Dashboard_Page;
use Notification_Hub\Presenters\Admin\Hooks_Page;
use Notification_Hub\Presenters\Admin\Settings_Page;

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
	 * Constructor - register all services.
	 */
	public function __construct() {
		$this->register_repositories();
		$this->register_services();
		$this->register_presenters();
	}

	/**
	 * Register repositories.
	 *
	 * @return void
	 */
	private function register_repositories() {
		// Notifications Repository.
		$this->set(
			'notifications_repo',
			function () {
				return new Notifications();
			}
		);

		// Custom Hooks Repository.
		$this->set(
			'custom_hooks_repo',
			function () {
				return new Custom_Hooks();
			}
		);
	}

	/**
	 * Register services.
	 *
	 * @return void
	 */
	private function register_services() {
		// Notification Dispatcher.
		$this->set(
			'notification_dispatcher',
			function () {
				return new Notification_Dispatcher();
			}
		);
	}

	/**
	 * Register presenters.
	 *
	 * @return void
	 */
	private function register_presenters() {
		// Dashboard Page Presenter.
		$this->set(
			'dashboard_presenter',
			function () {
				return new Dashboard_Page( $this->get( 'notifications_repo' ) );
			}
		);

		// Hooks Page Presenter.
		$this->set(
			'hooks_presenter',
			function () {
				return new Hooks_Page( $this->get( 'custom_hooks_repo' ) );
			}
		);

		// Settings Page Presenter.
		$this->set(
			'settings_presenter',
			function () {
				return new Settings_Page();
			}
		);
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
