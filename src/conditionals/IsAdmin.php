<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if current request is within WP admin.
 *
 * @since 1.7.2
 */
final class IsAdmin implements Conditional {
    public function passes(): bool {
        return is_admin();
    }
}
