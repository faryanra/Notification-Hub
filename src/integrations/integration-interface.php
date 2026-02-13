<?php
/**
 * Integration Interface
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Integration_Interface {
	public function register(): void;
}
