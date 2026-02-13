<?php
/**
 * Hook Manager (Integration Loader)
 *
 * Registers and loads integrations with conditional support.
 * Inspired by Yoast SEO's Loader.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub;

use Notification_Hub\Integrations\Admin\Menu_Registration;
use Notification_Hub\Integrations\Admin\Settings_Registration;
use Notification_Hub\Integrations\Admin\Admin_Assets;
use Notification_Hub\Integrations\Admin\Admin_Bar_Badge;
use Notification_Hub\Integrations\Events\WordPress\Comment_Posted;
use Notification_Hub\Integrations\Events\WordPress\Post_Status_Changed;
use Notification_Hub\Integrations\Events\WordPress\User_Registered;
use Notification_Hub\Integrations\Events\WordPress\Custom_Hooks_Loader;
use Notification_Hub\Integrations\Channels\Email_Sender;
use Notification_Hub\Conditionals\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader Class
 */
class Loader {

	/**
	 * DI Container.
	 *
	 * @var Main
	 */
	private $container;

	/**
	 * Integrations to load.
	 *
	 * @var array
	 */
	private $integrations = array();

	/**
	 * Constructor.
	 *
	 * @param Main $container DI Container.
	 */
	public function __construct( Main $container ) {
		$this->container = $container;
		$this->register_integrations();
	}

	/**
	 * Register all integrations.
	 *
	 * @return void
	 */
	private function register_integrations() {
		// Admin integrations (load only in admin)
		$this->integrations[] = array(
			'integration' => new Menu_Registration(
				$this->container->get( 'dashboard_presenter' ),
				$this->container->get( 'hooks_presenter' ),
				$this->container->get( 'settings_presenter' )
			),
			'conditionals' => array( Admin::class ),
		);

		$this->integrations[] = array(
			'integration'  => new Settings_Registration(),
			'conditionals' => array( Admin::class ),
		);

		$this->integrations[] = array(
			'integration'  => new Admin_Assets(),
			'conditionals' => array( Admin::class ),
		);

		$this->integrations[] = array(
			'integration'  => new Admin_Bar_Badge(),
			'conditionals' => array( Admin::class ),
		);

		// Event listeners (always load)
		$notifications_repo = $this->container->get( 'notifications_repo' );
		$dispatcher        = $this->container->get( 'notification_dispatcher' );
		$hooks_repo        = $this->container->get( 'custom_hooks_repo' );

		$this->integrations[] = array(
			'integration'  => new Comment_Posted( $notifications_repo, $dispatcher ),
			'conditionals' => array(),
		);

		$this->integrations[] = array(
			'integration'  => new Post_Status_Changed( $notifications_repo, $dispatcher ),
			'conditionals' => array(),
		);

		$this->integrations[] = array(
			'integration'  => new User_Registered( $notifications_repo, $dispatcher ),
			'conditionals' => array(),
		);

		$this->integrations[] = array(
			'integration'  => new Custom_Hooks_Loader( $hooks_repo, $notifications_repo, $dispatcher ),
			'conditionals' => array(),
		);

		// Channels (always load)
		$this->integrations[] = array(
			'integration'  => new Email_Sender(),
			'conditionals' => array(),
		);
	}

	/**
	 * Load all integrations.
	 *
	 * @return void
	 */
	public function load() {
		foreach ( $this->integrations as $item ) {
			$integration  = $item['integration'];
			$conditionals = isset( $item['conditionals'] ) ? $item['conditionals'] : array();

			if ( ! $this->should_load( $conditionals ) ) {
				continue;
			}

			if ( method_exists( $integration, 'register' ) ) {
				$integration->register();
			}
		}
	}

	/**
	 * Check if integration should load.
	 *
	 * @param array $conditionals Conditional class names.
	 * @return bool
	 */
	private function should_load( array $conditionals ) {
		if ( empty( $conditionals ) ) {
			return true;
		}

		foreach ( $conditionals as $conditional_class ) {
			if ( ! class_exists( $conditional_class ) ) {
				continue;
			}

			$conditional = new $conditional_class();

			if ( ! method_exists( $conditional, 'is_met' ) ) {
				continue;
			}

			if ( ! $conditional->is_met() ) {
				return false;
			}
		}

		return true;
	}
}
