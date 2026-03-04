<?php

namespace NotificationHub\Helpers;

/**
 * Sanitization helper.
 *
 * @since 1.7.2
 */
final class Sanitization {
    public static function text($value): string {
        return sanitize_text_field((string) $value);
    }

    public static function key($value): string {
        return sanitize_key((string) $value);
    }

    /**
     * @param mixed $value
     * @return array<int,string>
     */
    public static function keysArray($value): array {
        $arr = is_array($value) ? $value : [];
        return array_values(array_filter(array_map('sanitize_key', $arr)));
    }
}
