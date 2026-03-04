<?php

namespace NotificationHub\Conditionals;

/**
 * Checks if current request is an AJAX call.
 *
 * @since 1.7.2
 */
final class IsAjax implements Conditional {
    public function passes(): bool {
        return function_exists('wp_doing_ajax') ? wp_doing_ajax() : (defined('DOING_AJAX') && DOING_AJAX);
    }
}
