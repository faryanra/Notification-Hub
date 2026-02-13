<?php
/**
 * Bootstrap (Entry Point)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Load autoloader
require_once __DIR__ . '/autoloader.php';

// 2. Load core
require_once __DIR__ . '/main.php';
require_once __DIR__ . '/loader.php';

// 3. Boot (same as before)
function nh_boot_v2() {
	$container = \Notification_Hub\Main::get();

	// TODO: Register services

	$loader = new \Notification_Hub\Loader( $container );
	$loader->boot();
}
add_action( 'plugins_loaded', 'nh_boot_v2', 5 );
