<?php
// NH v1.2.0 — Simple license checker

if (!defined('ABSPATH')) exit;

class NH_License {
    public static function is_pro() {
        $key = get_option('nh_license_key', '');
        return !empty($key) && strlen($key) > 8; // شرط ساده برای تست
    }
}
