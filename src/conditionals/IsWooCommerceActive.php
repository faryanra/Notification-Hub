<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if WooCommerce is active.
 *
 * @since 1.7.2
 */
final class IsWooCommerceActive implements Conditional {
    public function passes(): bool {
        return class_exists('WooCommerce') || defined('WC_VERSION');
    }
}
