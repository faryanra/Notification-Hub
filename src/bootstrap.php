<?php
/**
 * Bootstrap for New Architecture
 *
 * Initializes DI Container and loads integrations.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load autoloader
require_once __DIR__ . '/autoloader.php';

// Initialize DI Container
$container = new Main();

// Load integrations via Hook Manager
$loader = new Loader( $container );
$loader->load();
