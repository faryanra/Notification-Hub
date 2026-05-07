<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Calculates notification priority.
 *
 * @since 1.0.0
 */
final class PriorityCalculator {
    /**
     * @param string $source
     * @param string $type
     */
    public function infer(string $source, string $type): int {
        $src = strtolower($source);
        $typ = strtolower($type);

        $has = static function (string $haystack, string $needle): bool {
            return $needle !== '' && strpos($haystack, $needle) !== false;
        };

        if ($has($src, 'woocommerce') || $has($typ, 'order')) {
            return 80;
        }
        if ($has($typ, 'comment')) {
            return 60;
        }
        if ($has($src, 'cf7') || $has($typ, 'form') || $has($typ, 'cf7')) {
            return 55;
        }
        if ($has($src, 'security') || $has($src, 'wordfence') || $has($typ, 'security') || $has($typ, 'error')) {
            return 90;
        }

        return 50;
    }
}

