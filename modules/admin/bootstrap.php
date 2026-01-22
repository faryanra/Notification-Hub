<?php
/**
 * Admin module bootstrap.
 *
 * Only registers hooks / wires dependencies.
 */

defined('ABSPATH') || exit;

return function ($r, $context = 'admin') {
    if ($context !== 'admin') {
        return;
    }

    // Existing v1.7.1 admin wiring (kept for now; will be refactored into smaller classes).
    if (class_exists('NH_Admin_UI')) {
        new NH_Admin_UI($r);
    }

    if (class_exists('NH_Custom_Hooks') && method_exists('NH_Custom_Hooks', 'init')) {
        NH_Custom_Hooks::init($r);
    }
};
