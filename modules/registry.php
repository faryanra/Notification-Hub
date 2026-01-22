<?php
/**
 * Module registry.
 *
 * Intentionally lightweight: it only declares which module bootstraps
 * should be loaded by the core loader.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

return [
    /**
     * Core/Admin UI.
     *
     * @since 1.7.2
     */
    'admin'     => __DIR__ . '/admin/bootstrap.php',

    /**
     * Licensing (Premium; refactor priority).
     *
     * @since 1.7.2
     */
    'license'   => __DIR__ . '/license/bootstrap.php',

    /**
     * Notifications & queue.
     *
     * @since 1.7.2
     */
    'notifier'  => __DIR__ . '/notifier/bootstrap.php',

    /**
     * Dashboard.
     *
     * @since 1.7.2
     */
    'dashboard' => __DIR__ . '/dashboard/bootstrap.php',
];
