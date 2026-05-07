<?php
namespace NotificationHub\Conditionals;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks if current request is within WP admin.
 *
 * @since 1.0.0
 */
final class IsAdmin implements Conditional {
    public function passes(): bool {
        return is_admin();
    }
}

