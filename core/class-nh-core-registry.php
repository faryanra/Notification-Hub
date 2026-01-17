<?php
/**
 * NH_Core_Registry
 *
 * Simple singleton service container for sharing plugin services.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Core_Registry {

    /**
     * Singleton instance.
     *
     * @since 1.6.2
     * @var NH_Core_Registry|null
     */
    private static $instance = null;

    /**
     * Stored services.
     *
     * @since 1.6.2
     * @var array<string, mixed>
     */
    private $services = [];

    /**
     * Prevent direct instantiation.
     *
     * @since 1.6.2
     */
    private function __construct() {}

    /**
     * Get singleton instance.
     *
     * @since 1.6.2
     * @return NH_Core_Registry
     */
    public static function get() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Store a service by key.
     *
     * @since 1.6.2
     * @param string $key Service key.
     * @param mixed  $svc Service instance/value.
     * @return void
     */
    public function set($key, $svc) {
        $this->services[$key] = $svc;
    }

    /**
     * Get a service by key.
     *
     * @since 1.6.2
     * @param string $key Service key.
     * @return mixed|null
     */
    public function get_svc($key) {
        return $this->services[$key] ?? null;
    }
}