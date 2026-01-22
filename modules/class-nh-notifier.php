<?php
/**
 * NH_Notifier
 *
 * Facade notification dispatcher that routes messages to channel handlers.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Split implementation classes.
$base = __DIR__ . '/notifier/';

if (file_exists($base . 'class-nh-notifier-loader.php')) {
    require_once $base . 'class-nh-notifier-loader.php';
}

if (file_exists($base . 'class-nh-notifier-dispatcher.php')) {
    require_once $base . 'class-nh-notifier-dispatcher.php';
}

// Back-compat: keep NH_Notifier class name.
if (class_exists('NH_Notifier_Dispatcher') && !class_exists('NH_Notifier')) {
    class NH_Notifier extends NH_Notifier_Dispatcher {
        // No body; inherits everything.
    }
}

// Back-compat: load handlers on file load.
if (class_exists('NH_Notifier') && method_exists('NH_Notifier', 'load_handlers')) {
    NH_Notifier::load_handlers();
}