<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if Contact Form 7 is active.
 *
 * @since 1.7.2
 */
final class IsContactForm7Active implements Conditional {
    public function passes(): bool {
        return defined('WPCF7_VERSION') || class_exists('WPCF7');
    }
}
