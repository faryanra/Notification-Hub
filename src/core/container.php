<?php
/**
 * Dependency Injection Container
 *
 * Simple service container for dependency management.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Container {

	private static $instance = null;
	private $services = array();
	private $singletons = array();

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register( string $name, callable $factory, bool $singleton = true ): void {
		$this->services[ $name ] = $factory;

		if ( $singleton ) {
			$this->singletons[ $name ] = true;
		}
	}

	public function get_svc( string $name ) {
		if ( ! isset( $this->services[ $name ] ) ) {
			return null;
		}

		$is_singleton = isset( $this->singletons[ $name ] );

		if ( $is_singleton && isset( $this->services[ $name . '_instance' ] ) ) {
			return $this->services[ $name . '_instance' ];
		}

		$service = call_user_func( $this->services[ $name ], $this );

		if ( $is_singleton ) {
			$this->services[ $name . '_instance' ] = $service;
		}

		return $service;
	}

	public function has( string $name ): bool {
		return isset( $this->services[ $name ] );
	}
}
