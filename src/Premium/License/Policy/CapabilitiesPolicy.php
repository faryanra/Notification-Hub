<?php

namespace NotificationHub\Premium\License\Policy;

/**
 * Capabilities policy for who can manage license settings.
 *
 * @since 1.7.2
 */
final class CapabilitiesPolicy {
    public function canManage(): bool {
        return current_user_can('manage_options');
    }
}
