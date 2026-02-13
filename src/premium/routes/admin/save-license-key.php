<?php
/**
 * Save License Key Route (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Routes\Admin;

use Notification_Hub\Premium\License\Admin\Actions\Save_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_License_Key {

	private $action;

	public function __construct( Save_Key $action ) {
		$this->action = $action;
	}

	public function handle() {
		$this->action->handle();
	}
}
