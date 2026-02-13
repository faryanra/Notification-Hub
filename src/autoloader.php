<?php
/**
 * PSR-4 Autoloader
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	function ( $class ) {
		$prefix   = 'Notification_Hub\\';
		$base_dir = __DIR__ . '/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// Convert to lowercase and add hyphens
		$file = strtolower( $file );
		$file = str_replace( '_', '-', $file );

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);
