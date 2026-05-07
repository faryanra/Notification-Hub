<?php
namespace NotificationHub\Integrations;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Loader;

/**
 * Integration contract.
 *
 * Integrations are wiring only (hooks/routes/assets registration).
 *
 * @since 1.0.0
 */
interface Integration {
    /**
     * Register hooks.
     *
     * @since 1.0.0
     * @param Loader $loader Loader instance.
     * @return void
     */
    public function register(Loader $loader): void;
}

