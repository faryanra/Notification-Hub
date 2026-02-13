<?php
/**
 * Hooks Page Presenter
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters\Admin;

use Notification_Hub\Repositories\Custom_Hooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks Page Presenter
 */
class Hooks_Page {

	/**
	 * Custom hooks repository.
	 *
	 * @var Custom_Hooks
	 */
	private $repo;

	/**
	 * Constructor.
	 *
	 * @param Custom_Hooks $repo Custom hooks repository.
	 */
	public function __construct( Custom_Hooks $repo ) {
		$this->repo = $repo;
	}

	/**
	 * Render page.
	 *
	 * @return void
	 */
	public function render() {
		$hooks = $this->repo->get_all();

		include NH_PLUGIN_DIR . 'templates/admin/hooks.php';
	}
}
