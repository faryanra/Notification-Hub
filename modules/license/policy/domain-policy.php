<?php
/**
 * Domain policy helpers for license.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Domain_Policy {

    /**
     * Normalize a domain string.
     *
     * @since 1.7.2
     * @param string $domain Domain.
     * @return string
     */
    public static function normalize_domain(string $domain): string {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);
        return is_string($domain) ? $domain : '';
    }

    /**
     * Compare domains.
     *
     * @since 1.7.2
     * @param string $a Domain A.
     * @param string $b Domain B.
     * @return bool
     */
    public static function is_domain_match(string $a, string $b): bool {
        $a = self::normalize_domain($a);
        $b = self::normalize_domain($b);
        return $a !== '' && $b !== '' && $a === $b;
    }
}
