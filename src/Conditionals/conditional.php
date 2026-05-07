<?php
namespace NotificationHub\Conditionals;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * A simple conditional used to decide whether an integration should run.
 *
 * @since 1.0.0
 */
interface Conditional {
    /**
     * Determine if the condition passes.
     *
     * @since 1.0.0
     */
    public function passes(): bool;
}

