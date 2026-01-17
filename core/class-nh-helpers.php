<?php
/**
 * NH_Helpers
 *
 * Small helper utilities used across Notification Hub (logging, formatting, safe helpers).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Helpers {

    /**
     * Structured logger for Notification Hub modules (debug-only).
     *
     * Usage:
     * - NH_Helpers::log('Something happened');
     * - NH_Helpers::log(['data' => 'value'], 'debug');
     *
     * @since 1.6.2
     * @param mixed  $msg   Message (string/array/object).
     * @param string $level Log level (info|debug|error|warn).
     * @return void
     */
    public static function log($msg, string $level = 'info'): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $level = strtoupper(sanitize_key($level));
        if ($level === '') {
            $level = 'INFO';
        }

        if (!is_string($msg)) {
            $msg = wp_json_encode($msg);
        }

        $prefix = sprintf('[%s][NH]', $level);

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log($prefix . ' ' . (string) $msg);
    }

    /**
     * JSON pretty print (useful for debugging/logging).
     *
     * @since 1.6.2
     * @param mixed $data Any data.
     * @return string JSON string.
     */
    public static function json_pretty($data): string {
        return (string) wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Safe string truncation (UTF-8 friendly when mbstring is available).
     *
     * @since 1.6.2
     * @param mixed $text  Input text.
     * @param int   $limit Max length.
     * @return string Truncated string.
     */
    public static function truncate($text, int $limit = 200): string {
        $text = (string) $text;
        $limit = max(0, $limit);

        if ($limit === 0) {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return (mb_strlen($text, 'UTF-8') > $limit)
                ? (mb_substr($text, 0, $limit, 'UTF-8') . '…')
                : $text;
        }

        return (strlen($text) > $limit)
            ? (substr($text, 0, $limit) . '…')
            : $text;
    }
}