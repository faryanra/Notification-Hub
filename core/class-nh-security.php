<?php
// NH v1.2.0 — Security helpers

if (!defined('ABSPATH')) exit;

class NH_Security {
    public function verify_admin() {
        // NH v1.2.0 — Capability check
        return current_user_can('manage_options');
    }
    public function verify_nonce($nonce) {
        // NH v1.2.0 — Nonce check
        return wp_verify_nonce($nonce, 'nh_admin');
    }
    public function sanitize_text($str){ return sanitize_text_field($str); }
}
