<?php
namespace NotificationHub\Conditionals;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks if Contact Form 7 is active.
 *
 * @since 1.0.0
 */
final class IsContactForm7Active implements Conditional {
    public function passes(): bool {
        return defined('WPCF7_VERSION') || class_exists('WPCF7');
    }
}

