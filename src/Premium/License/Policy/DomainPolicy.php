<?php

namespace NotificationHub\Premium\License\Policy;

/**
 * Domain policy for license activation.
 *
 * @since 1.7.2
 */
final class DomainPolicy {
    public function currentDomain(): string {
        $home = (string) home_url();
        $host = (string) wp_parse_url($home, PHP_URL_HOST);
        return $host !== '' ? $host : 'localhost';
    }
}
