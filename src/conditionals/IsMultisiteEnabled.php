<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if multisite is enabled.
 *
 * @since 1.7.2
 */
final class IsMultisiteEnabled implements Conditional {
    public function passes(): bool {
        return function_exists('is_multisite') && is_multisite();
    }
}
