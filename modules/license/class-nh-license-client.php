<?php
/**
 * License remote client.
 *
 * HTTP calls + response parsing for the license server.
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_License_Client {

    /**
     * Remote verification.
     *
     * @since 1.7.1
     */
    public static function remote_verify(string $key, string $server_url, bool $debug = false): array {
        $key = strtoupper(trim($key));
        $server_url = trim($server_url);

        if ($key === '' || $server_url === '') {
            return [
                'ok' => false,
                'message' => 'License key or server URL missing.',
                'state' => [],
            ];
        }

        $domain = NH_License::get_current_domain();
        $site_id = md5($domain . '|' . wp_salt('auth'));

        $payload = [
            'product' => 'notification-hub',
            'license_key' => $key,
            'domain' => $domain,
            'site_id' => $site_id,
        ];

        $ua = 'NotificationHub/' . (defined('NH_VERSION') ? NH_VERSION : 'dev') . '; ' . home_url('/');

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent' => $ua,
        ];

        $response = wp_remote_post($server_url, [
            'timeout' => 15,
            'redirection' => 5,
            'headers' => $headers,
            'body' => $payload,
        ]);

        $response_code = !is_wp_error($response) ? (int) wp_remote_retrieve_response_code($response) : 0;

        if (!is_wp_error($response) && $response_code === 403) {
            $get_url = add_query_arg($payload, $server_url);

            $response = wp_remote_get($get_url, [
                'timeout' => 15,
                'redirection' => 5,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => $ua,
                ],
            ]);
        }

        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'message' => $response->get_error_message(),
                'state' => [],
            ];
        }

        $raw = wp_remote_retrieve_body($response);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $code = (int) wp_remote_retrieve_response_code($response);
            $content_type = (string) wp_remote_retrieve_header($response, 'content-type');
            $snippet = substr((string) $raw, 0, 200);
            $snippet = preg_replace('/\s+/', ' ', (string) $snippet);

            $msg = 'Invalid JSON response from license server.';
            $msg .= ' HTTP ' . $code;
            if ($content_type !== '') {
                $msg .= ' (' . $content_type . ')';
            }
            if ($snippet !== '') {
                $msg .= ' First 200 chars: ' . $snippet;
            }

            if ($debug && defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('[NH_License_Client] verify invalid_json: ' . $msg);
            }

            return [
                'ok' => false,
                'message' => $msg,
                'state' => [],
            ];
        }

        $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : 'inactive';
        $features = isset($data['features']) && is_array($data['features']) ? $data['features'] : [];
        $message = isset($data['message']) && is_string($data['message']) ? $data['message'] : '';
        $grace_days = isset($data['grace_days']) ? (int) $data['grace_days'] : NH_License::GRACE_DAYS;

        $allowed = ['active', 'inactive', 'revoked', 'grace', 'banned', 'expired'];
        if (!in_array($status, $allowed, true)) {
            $status = 'inactive';
        }

        $state = [
            'status' => $status,
            'features' => $features,
            'domain' => $domain,
            'last_check' => time(),
            'message' => $message,
            'license_hash' => NH_License::hash_key($key),
        ];

        if ($status === 'grace') {
            $state['grace_until'] = time() + max(1, $grace_days) * DAY_IN_SECONDS;
        } else {
            $state['grace_until'] = 0;
        }

        return [
            'ok' => ($status === 'active' || $status === 'grace'),
            'message' => $message !== '' ? $message : 'License server response.',
            'state' => $state,
        ];
    }
}