<?php
// NH v1.2.0 — Service Container (Registry)

if (!defined('ABSPATH')) exit;

class NH_Core_Registry {
    private static $instance;
    private $services = [];

    public static function get() {
        return self::$instance ?: (self::$instance = new self());
    }

    public function set($key, $svc) { $this->services[$key] = $svc; }
    public function get_svc($key)    { return $this->services[$key] ?? null; }
}
