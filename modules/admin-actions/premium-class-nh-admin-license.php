<?php
/**
 * Premium-prefixed loader for NH_Admin_License
 *
 * This file exists so Premium ZIP extraction can include only premium-* files,
 * while keeping the actual class implementation in a single source of truth.
 *
 * IMPORTANT:
 * - Do NOT declare NH_Admin_License here.
 * - This file should only include the real implementation if it's not already loaded.
 *
 * @package Notification_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NH_Admin_License')) {
    $impl = __DIR__ . '/class-nh-admin-license.php';
    if (file_exists($impl)) {
        require_once $impl;
    }
}