<?php
/**
 * Revoke License Route (Premium)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Premium\Routes\Admin;

use Notification_Hub\Premium\License\Admin\Actions\Revoke;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Revoke_License {

	private $action;

	public function __construct( Revoke $action ) {
		$this->action = $action;
	}

	public function handle() {
		$this->action->handle();
	}
}
