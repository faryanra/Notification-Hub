<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if premium (pro) plugin is active.
 *
 * We keep this heuristic simple and non-fatal.
 *
 * @since 1.7.2
 */
final class IsPremiumActive implements Conditional {
    public function passes(): bool {
        return defined('NH_PRO_VERSION')
            || class_exists('NotificationHubPro')
            || class_exists('NH_Pro')
            || function_exists('notification_hub_pro');
    }
}
