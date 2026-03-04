<?php

namespace NotificationHub\Premium\License\Http;

use WP_Error;

/**
 * License server client.
 *
 * @since 1.7.2
 */
final class LicenseClient {
    private string $baseUrl;

    public function __construct(string $baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @return array<string,mixed>|WP_Error
     */
    public function validate(string $key, string $domain) {
        $url = $this->baseUrl . '/validate';

        $res = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'key'    => $key,
                'domain' => $domain,
            ]),
        ]);

        if (is_wp_error($res)) {
            return $res;
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        $body = (string) wp_remote_retrieve_body($res);
        $json = json_decode($body, true);

        if ($code < 200 || $code >= 300) {
            return new WP_Error('nh_license_http_error', 'License server error', [
                'status' => $code,
                'body'   => $body,
            ]);
        }

        return is_array($json) ? $json : ['ok' => true];
    }
}
