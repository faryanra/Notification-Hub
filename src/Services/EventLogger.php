<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Structured plugin logger.
 *
 * Writes to wp_nh_logs table when available.
 *
 * @since 1.0.0
 */
final class EventLogger {
    /**
     * @param array<string,mixed> $context
     */
    public static function info(string $scope, string $event, string $message, array $context = []): void {
        self::write('info', $scope, $event, $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public static function warn(string $scope, string $event, string $message, array $context = []): void {
        self::write('warn', $scope, $event, $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public static function error(string $scope, string $event, string $message, array $context = []): void {
        self::write('error', $scope, $event, $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public static function write(string $level, string $scope, string $event, string $message, array $context = []): void {
        global $wpdb;

        if (!isset($wpdb) || !is_object($wpdb)) {
            return;
        }

        $table = $wpdb->prefix . 'nh_logs';
        $clean = self::sanitizeContext($context);

        $payload = [
            'created_at' => current_time('mysql'),
            'level'      => self::cleanToken($level, 'info', 10),
            'scope'      => self::cleanToken($scope, 'general', 32),
            'event'      => self::cleanToken($event, 'event', 64),
            'message'    => self::cleanMessage($message),
            'context'    => wp_json_encode($clean),
        ];

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $ok = $wpdb->insert(
            $table,
            $payload,
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($ok === false && defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log(sprintf('Notification Hub log write failed: %s/%s', $payload['scope'], $payload['event']));
        }
    }

    private static function cleanToken(string $value, string $fallback, int $maxLen): string {
        $v = sanitize_key($value);
        if ($v === '') {
            $v = $fallback;
        }

        if (strlen($v) > $maxLen) {
            $v = substr($v, 0, $maxLen);
        }

        return $v;
    }

    private static function cleanMessage(string $message): string {
        $clean = sanitize_text_field($message);
        if ($clean === '') {
            $clean = 'log';
        }

        if (strlen($clean) > 500) {
            $clean = substr($clean, 0, 500);
        }

        return $clean;
    }

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    private static function sanitizeContext(array $context): array {
        $result = [];
        foreach ($context as $key => $value) {
            $cleanKey = sanitize_key((string) $key);
            if ($cleanKey === '') {
                continue;
            }

            if (self::isSensitiveKey($cleanKey)) {
                $result[$cleanKey] = '[redacted]';
                continue;
            }

            $result[$cleanKey] = self::sanitizeValue($value);
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function sanitizeValue($value) {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $cleanKey = is_string($k) ? sanitize_key($k) : (string) $k;
                if ($cleanKey === '') {
                    continue;
                }

                if (self::isSensitiveKey($cleanKey)) {
                    $out[$cleanKey] = '[redacted]';
                    continue;
                }

                $out[$cleanKey] = self::sanitizeValue($v);
            }
            return $out;
        }

        if (is_object($value)) {
            return self::sanitizeValue((array) $value);
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $str = sanitize_text_field((string) $value);
        if (strlen($str) > 500) {
            $str = substr($str, 0, 500);
        }
        return $str;
    }

    private static function isSensitiveKey(string $key): bool {
        $parts = ['secret', 'token', 'password', 'signature', 'webhook', 'authorization', 'license_key', 'api_key'];
        foreach ($parts as $part) {
            if (strpos($key, $part) !== false) {
                return true;
            }
        }

        return false;
    }
}


