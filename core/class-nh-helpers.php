<?php
// NH v1.2.0 — Generic helpers

if (!defined('ABSPATH')) exit;

class NH_Helpers {
    public function esc_html($s){ return esc_html($s); }
    public function now(){ return current_time('mysql'); }
}
