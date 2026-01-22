<?php
/**
 * Dashboard module bootstrap.
 *
 * Only registers hooks / wires dependencies.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

return function ($r, $context = 'admin') {
    if ($context !== 'admin') {
        return;
    }

    if (class_exists('NH_Dashboard') && method_exists('NH_Dashboard', 'init')) {
        NH_Dashboard::init($r);
    }
};
