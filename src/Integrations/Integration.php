<?php

namespace NotificationHub\Integrations;

use NotificationHub\Loader;

/**
 * Integration contract.
 *
 * Integrations are wiring only (hooks/routes/assets registration).
 *
 * @since 1.7.2
 */
interface Integration {
    /**
     * Register hooks.
     *
     * @since 1.7.2
     * @param Loader $loader Loader instance.
     * @return void
     */
    public function register(Loader $loader): void;
}
