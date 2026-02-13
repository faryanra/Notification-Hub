<?php
/**
 * Save License Bundle Route (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Routes\Admin;

use Notification_Hub\Premium\License\Admin\Actions\Save_Bundle;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_License_Bundle {

	private $action;

	public function __construct( Save_Bundle $action ) {
		$this->action = $action;
	}

	public function handle() {
		$this->action->handle();
	}
}
