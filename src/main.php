<?php
/**
 * Main Container (DI)
 *
 * Simple singleton service container for sharing plugin services.
 * (Refactored from NH_Core_Registry)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Main {

	private static $instance = null;
	private $services = array();

	private function __construct() {}

	public static function get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set( $key, $svc ) {
		$this->services[ $key ] = $svc;
	}

	public function get_svc( $key ) {
		return $this->services[ $key ] ?? null;
	}
}
