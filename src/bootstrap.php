<?php
/**
 * Bootstrap for New Architecture (v2.0.0)
 *
 * Initializes DI container, autoloader, and Hook Manager.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load PSR-4 autoloader.
require_once __DIR__ . '/autoloader.php';

// Load DI Container.
require_once __DIR__ . '/main.php';

// Load Hook Manager.
require_once __DIR__ . '/loader.php';

use Notification_Hub\Main;
use Notification_Hub\Loader;

/**
 * Initialize new architecture.
 *
 * @return void
 */
function nh_init_v2() {
	// Initialize DI Container.
	$container = new Main();

	// Initialize Hook Manager.
	$loader = new Loader( $container );
	$loader->load();
}

add_action( 'plugins_loaded', 'nh_init_v2', 3 );
