<?php
namespace NotificationHub\Conditionals;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks if current user can manage options.
 *
 * Note: In REST/AJAX contexts user might not be logged in; then this will be false.
 *
 * @since 1.0.0
 */
final class CanManageOptions implements Conditional {
    public function passes(): bool {
        return function_exists('current_user_can') && current_user_can('manage_options');
    }
}

