<?php

namespace NotificationHub\Conditionals;

/**
 * A simple conditional used to decide whether an integration should run.
 *
 * @since 1.7.2
 */
interface Conditional {
    /**
     * Determine if the condition passes.
     *
     * @since 1.7.2
     */
    public function passes(): bool;
}
