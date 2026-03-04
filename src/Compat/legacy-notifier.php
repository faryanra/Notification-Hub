<?php

/**
 * Legacy Notifier entrypoint (compat).
 *
 * Defines NH_Notifier_* classes for old code.
 *
 * @since 1.7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NH_Notifier_Loader')) {
    class NH_Notifier_Loader {
        public static function load(): void {
            // No-op.
        }
    }
}

if (!class_exists('NH_Notifier_Dispatcher')) {
    class NH_Notifier_Dispatcher extends \NotificationHub\Compat\NotifierDispatcher {}
}

if (!class_exists('NH_Notifier')) {
    class NH_Notifier extends NH_Notifier_Dispatcher {}
}
