<?php
/**
 * Admin Actions Coordinator
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

// Load sub-modules
require_once __DIR__ . '/admin-actions/class-nh-admin-license.php';
require_once __DIR__ . '/admin-actions/class-nh-admin-hooks.php';
require_once __DIR__ . '/admin-actions/class-nh-admin-csv-export.php';

class NH_Admin_Actions {

    /**
     * Initialize all admin action handlers
     */
    public static function init() {
        NH_Admin_License::init();
        NH_Admin_Hooks::init();
        NH_Admin_CSV_Export::init();
    }
}

// Boot
add_action('admin_init', ['NH_Admin_Actions', 'init']);
