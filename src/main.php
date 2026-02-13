<?php
/**
 * Dependency Injection Container
 *
 * Central service registry for the plugin.
 * Inspired by Yoast SEO's Main.php
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
 * Main DI Container
 */
class Main {

	/**
	 * Services container.
	 *
	 * @var array
	 */
	private $services = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->register_services();
	}

	/**
	 * Register all services.
	 *
	 * @return void
	 */
	private function register_services() {
		// Repositories
		$this->services['notifications_repo'] = function() {
			return new Notifications();
		};

		$this->services['custom_hooks_repo'] = function() {
			return new Custom_Hooks();
		};

		// Services
		$this->services['notification_dispatcher'] = function() {
			return new Notification_Dispatcher();
		};

		// Presenters
		$this->services['dashboard_presenter'] = function() {
			return new Dashboard_Page( $this->get( 'notifications_repo' ) );
		};

		$this->services['hooks_presenter'] = function() {
			return new Hooks_Page( $this->get( 'custom_hooks_repo' ) );
		};

		$this->services['settings_presenter'] = function() {
			return new Settings_Page();
		};
	}

	/**
	 * Get service.
	 *
	 * @param string $name Service name.
	 * @return mixed
	 */
	public function get( $name ) {
		if ( ! isset( $this->services[ $name ] ) ) {
			return null;
		}

		// Lazy initialization
		if ( is_callable( $this->services[ $name ] ) ) {
			$this->services[ $name ] = call_user_func( $this->services[ $name ] );
		}

		return $this->services[ $name ];
	}

	/**
	 * Check if service exists.
	 *
	 * @param string $name Service name.
	 * @return bool
	 */
	public function has( $name ) {
		return isset( $this->services[ $name ] );
	}
}
