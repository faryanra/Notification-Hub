<?php
/**
 * Admin Menu Registration
 *
 * Registers admin menu pages.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Admin;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Presenters\Admin\Dashboard_Page;
use Notification_Hub\Presenters\Admin\Hooks_Page;
use Notification_Hub\Presenters\Admin\Settings_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Registration
 */
class Menu_Registration implements Integration_Interface {

	/**
	 * Dashboard presenter.
	 *
	 * @var Dashboard_Page
	 */
	private $dashboard;

	/**
	 * Hooks presenter.
	 *
	 * @var Hooks_Page
	 */
	private $hooks;

	/**
	 * Settings presenter.
	 *
	 * @var Settings_Page
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Dashboard_Page $dashboard Dashboard presenter.
	 * @param Hooks_Page     $hooks     Hooks presenter.
	 * @param Settings_Page  $settings  Settings presenter.
	 */
	public function __construct( Dashboard_Page $dashboard, Hooks_Page $hooks, Settings_Page $settings ) {
		$this->dashboard = $dashboard;
		$this->hooks     = $hooks;
		$this->settings  = $settings;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	/**
	 * Register admin menus.
	 *
	 * @return void
	 */
	public function register_menus() {
		add_menu_page(
			__( 'Notification Hub', 'notification-hub' ),
			__( 'Notifications', 'notification-hub' ),
			'manage_options',
			'notification-hub',
			array( $this->dashboard, 'render' ),
			'dashicons-bell',
			30
		);

		add_submenu_page(
			'notification-hub',
			__( 'Dashboard', 'notification-hub' ),
			__( 'Dashboard', 'notification-hub' ),
			'manage_options',
			'notification-hub',
			array( $this->dashboard, 'render' )
		);

		add_submenu_page(
			'notification-hub',
			__( 'Custom Hooks', 'notification-hub' ),
			__( 'Custom Hooks', 'notification-hub' ),
			'manage_options',
			'notification-hub-hooks',
			array( $this->hooks, 'render' )
		);

		add_submenu_page(
			'notification-hub',
			__( 'Settings', 'notification-hub' ),
			__( 'Settings', 'notification-hub' ),
			'manage_options',
			'notification-hub-settings',
			array( $this->settings, 'render' )
		);
	}
}
