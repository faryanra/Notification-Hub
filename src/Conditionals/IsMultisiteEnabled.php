<?php
namespace NotificationHub\Conditionals;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks if multisite is enabled.
 *
 * @since 1.0.0
 */
final class IsMultisiteEnabled implements Conditional {
    public function passes(): bool {
        return function_exists('is_multisite') && is_multisite();
    }
}

