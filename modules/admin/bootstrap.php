<?php
/**
 * Admin module bootstrap.
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

    // Legacy wiring (will be refactored into smaller classes).
    if (class_exists('NH_Admin_UI')) {
        new NH_Admin_UI($r);
    }

    if (class_exists('NH_Custom_Hooks') && method_exists('NH_Custom_Hooks', 'init')) {
        NH_Custom_Hooks::init($r);
    }
};
