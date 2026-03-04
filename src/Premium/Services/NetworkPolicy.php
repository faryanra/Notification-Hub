<?php

namespace NotificationHub\Premium\Services;

use NotificationHub\Conditionals\IsMultisiteEnabled;

/**
 * Network policy for multisite installs.
 *
 * @since 1.7.2
 */
final class NetworkPolicy {
    public function isMultisite(): bool {
        return (new IsMultisiteEnabled())->passes();
    }

    public function isNetworkAdmin(): bool {
        return function_exists('is_network_admin') && is_network_admin();
    }

    public function canManageNetwork(): bool {
        // On multisite, network settings should be managed from network admin.
        return $this->isMultisite() ? ($this->isNetworkAdmin() && current_user_can('manage_network_options')) : current_user_can('manage_options');
    }
}
