<?php

namespace NotificationHub\Premium\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Premium\Services\NetworkPolicy;

/**
 * Multisite network settings wiring (placeholder).
 *
 * This file exists to match the new tree and will be expanded once
 * the Premium UI templates are migrated.
 *
 * @since 1.7.2
 */
final class MultisiteNetworkSettings implements Integration {
    public function register(Loader $loader): void {
        $policy = new NetworkPolicy();
        if (!$policy->isMultisite()) {
            return;
        }

        // Placeholder: later we will add network_admin_menu and settings registration.
        // $loader->addAction('network_admin_menu', [$this, 'registerMenu']);
    }
}
