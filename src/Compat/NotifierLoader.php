<?php

namespace NotificationHub\Compat;

use NotificationHub\Premium\Services\NetworkPolicy;

/**
 * Back-compat notifier loader.
 *
 * @since 1.7.2
 */
final class NotifierLoader {
    public static function load(): void {
        // No-op in new architecture; handlers are services.
    }
}
