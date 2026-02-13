<?php
/**
 * Save License Server Route (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Routes\Admin;

use Notification_Hub\Premium\License\Admin\Actions\Save_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_License_Server {

	private $action;

	public function __construct( Save_Server $action ) {
		$this->action = $action;
	}

	public function handle() {
		$this->action->handle();
	}
}
