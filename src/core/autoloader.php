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
		$base_dir = NH_PLUGIN_DIR . 'src/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		$file           = strtolower( str_replace( '_', '-', $file ) );

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);
