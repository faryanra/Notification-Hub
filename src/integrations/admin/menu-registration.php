<?php
/**
 * Menu Registration Integration
 *
 * Registers admin menu pages for Notification Hub.
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
 * Menu_Registration Class
 */
class Menu_Registration implements Integration_Interface {

	/**
	 * Dashboard page presenter.
	 *
	 * @var Dashboard_Page
	 */
	private $dashboard_page;

	/**
	 * Hooks page presenter.
	 *
	 * @var Hooks_Page
	 */
	private $hooks_page;

	/**
	 * Settings page presenter.
	 *
	 * @var Settings_Page
	 */
	private $settings_page;

	/**
	 * Constructor.
	 *
	 * @param Dashboard_Page $dashboard_page Dashboard page presenter.
	 * @param Hooks_Page     $hooks_page     Hooks page presenter.
	 * @param Settings_Page  $settings_page  Settings page presenter.
	 */
	public function __construct(
		Dashboard_Page $dashboard_page,
		Hooks_Page $hooks_page,
		Settings_Page $settings_page
	) {
		$this->dashboard_page = $dashboard_page;
		$this->hooks_page     = $hooks_page;
		$this->settings_page  = $settings_page;
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
			esc_html__( 'Notification Hub', 'notification-hub' ),
			esc_html__( 'Notification Hub', 'notification-hub' ),
			'manage_options',
			'nh-dashboard',
			array( $this->dashboard_page, 'render' ),
			'dashicons-bell',
			58
		);

		add_submenu_page(
			'nh-dashboard',
			esc_html__( 'Dashboard', 'notification-hub' ),
			esc_html__( 'Dashboard', 'notification-hub' ),
			'manage_options',
			'nh-dashboard',
			array( $this->dashboard_page, 'render' )
		);

		add_submenu_page(
			'nh-dashboard',
			esc_html__( 'Hooks', 'notification-hub' ),
			esc_html__( 'Hooks', 'notification-hub' ),
			'manage_options',
			'nh-hooks',
			array( $this->hooks_page, 'render' )
		);

		add_submenu_page(
			'nh-dashboard',
			esc_html__( 'Settings', 'notification-hub' ),
			esc_html__( 'Settings', 'notification-hub' ),
			'manage_options',
			'nh_settings',
			array( $this->settings_page, 'render' )
		);
	}
}
