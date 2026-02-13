<?php
/**
 * Dashboard Page Presenter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin;

use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Page Presenter
 */
class Dashboard_Page {

	/**
	 * Notifications repository.
	 *
	 * @var Notifications
	 */
	private $repo;

	/**
	 * Constructor.
	 *
	 * @param Notifications $repo Notifications repository.
	 */
	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Render page.
	 *
	 * @return void
	 */
	public function render() {
		$notifications = $this->repo->get_all();

		include NH_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}
}
