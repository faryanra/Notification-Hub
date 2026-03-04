<?php

namespace NotificationHub\Premium;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Premium\Integrations\Admin\AdminPostRegistration;
use NotificationHub\Premium\Integrations\Admin\MultisiteNetworkSettings;

/**
 * Premium bootstrap (registers premium-only integrations when available).
 *
 * @since 1.7.2
 */
final class Bootstrap implements Integration {
    public function register(Loader $loader): void {
        (new AdminPostRegistration())->register($loader);
        (new MultisiteNetworkSettings())->register($loader);
    }
}
