<?php
/**
 * Bootstrap
 *
 * Initializes all plugin components.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bootstrap {

	private $container;

	public function __construct() {
		$this->container = Container::instance();
	}

	public function init(): void {
		$this->register_services();
		$this->register_integrations();
	}

	private function register_services(): void {
		// Repositories
		$this->container->register(
			'repo_notifications',
			static function () {
				return new \Notification_Hub\Repositories\Notifications();
			}
		);

		$this->container->register(
			'repo_custom_hooks',
			static function () {
				return new \Notification_Hub\Repositories\Custom_Hooks();
			}
		);

		// Services
		$this->container->register(
			'email_sender',
			static function ( $c ) {
				return new \Notification_Hub\Integrations\Channels\Email_Sender( $c );
			}
		);

		$this->container->register(
			'notifier',
			static function ( $c ) {
				return new \Notification_Hub\Services\Notifier( $c );
			}
		);

		// Legacy compatibility
		$this->container->register(
			'db',
			static function () {
				return new \Notification_Hub\Repositories\Notifications();
			}
		);
	}

	private function register_integrations(): void {
		// Admin
		$this->register_integration( new \Notification_Hub\Integrations\Admin\Menu_Registration() );
		$this->register_integration( new \Notification_Hub\Integrations\Admin\Settings_Registration() );
		$this->register_integration( new \Notification_Hub\Integrations\Admin\Admin_Assets() );
		$this->register_integration( new \Notification_Hub\Integrations\Admin\Admin_Bar_Badge() );

		// WordPress Events
		$this->register_integration( new \Notification_Hub\Integrations\Events\WordPress\Comment_Posted( $this->container ) );
		$this->register_integration( new \Notification_Hub\Integrations\Events\WordPress\Post_Status_Changed( $this->container ) );
		$this->register_integration( new \Notification_Hub\Integrations\Events\WordPress\User_Registered( $this->container ) );
		$this->register_integration( new \Notification_Hub\Integrations\Events\WordPress\Custom_Hooks_Loader( $this->container ) );

		// WooCommerce
		if ( class_exists( 'WooCommerce' ) ) {
			$this->register_integration( new \Notification_Hub\Integrations\Events\WooCommerce\Order_Created( $this->container ) );
			$this->register_integration( new \Notification_Hub\Integrations\Events\WooCommerce\Low_Stock_Alert( $this->container ) );
		}

		// Contact Form 7
		if ( defined( 'WPCF7_VERSION' ) ) {
			$this->register_integration( new \Notification_Hub\Integrations\Events\Contact_Form_7\Form_Submitted( $this->container ) );
		}

		// Cron
		$this->register_integration( new \Notification_Hub\Integrations\Cron\Cleanup_Old_Notifications( $this->container ) );
		$this->register_integration( new \Notification_Hub\Integrations\Cron\Process_Queue( $this->container ) );

		// REST API
		$this->register_integration( new \Notification_Hub\Integrations\API\REST_API( $this->container ) );

		// Admin Routes
		$this->register_route( new \Notification_Hub\Routes\Admin\Mark_As_Read( $this->container ) );
		$this->register_route( new \Notification_Hub\Routes\Admin\Delete_Notification( $this->container ) );
		$this->register_route( new \Notification_Hub\Routes\Admin\Bulk_Actions( $this->container ) );
		$this->register_route( new \Notification_Hub\Routes\Admin\Save_Hook( $this->container ) );
		$this->register_route( new \Notification_Hub\Routes\Admin\Export_CSV( $this->container ) );
	}

	private function register_integration( $integration ): void {
		if ( method_exists( $integration, 'register' ) ) {
			$integration->register();
		}
	}

	private function register_route( $route ): void {
		if ( method_exists( $route, 'register' ) ) {
			$route->register();
		}
	}
}
