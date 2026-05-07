<?php
namespace NotificationHub\Conditionals;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks if WooCommerce is active.
 *
 * @since 1.0.0
 */
final class IsWooCommerceActive implements Conditional {
    public function passes(): bool {
        return class_exists('WooCommerce') || defined('WC_VERSION');
    }
}

