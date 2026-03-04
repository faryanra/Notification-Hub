<?php

namespace NotificationHub\Premium\License\Policy;

/**
 * Basic key format policy.
 *
 * @since 1.7.2
 */
final class KeyFormatPolicy {
    public function isValid(string $key): bool {
        $key = trim($key);
        if ($key === '') {
            return false;
        }

        // Allow letters, numbers, dashes, underscores.
        return (bool) preg_match('/^[A-Za-z0-9\-_]{10,200}$/', $key);
    }
}
