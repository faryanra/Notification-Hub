<?php

namespace NotificationHub\Routes\Api;

use NotificationHub\Repositories\NotificationsRepository;
use NotificationHub\Services\EventLogger;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST: Webhook endpoint to ingest a notification.
 *
 * Body example (JSON):
 * {"source":"external","type":"webhook","title":"...","message":"...","tags":["a"],"context":{}}
 *
 * @since 1.7.2
 */
final class Webhook {
    private const RATE_LIMIT_DEFAULT = 30;
    private const RATE_WINDOW_DEFAULT = 60;
    private const HMAC_WINDOW_DEFAULT = 300;

    public function handle(WP_REST_Request $request): WP_REST_Response {
        $ip = $this->resolveClientIp($request);

        if (!$this->allowByRateLimit($ip)) {
            EventLogger::warn('webhook', 'webhook_rate_limited', 'Webhook rate limit exceeded', [
                'ip' => $ip,
            ]);
            return new WP_REST_Response(['code' => 'rate_limited', 'message' => 'Too Many Requests'], 429);
        }

        $auth = $this->verifySignature($request, $ip);
        if ($auth !== true) {
            return $auth;
        }

        $body = $request->get_json_params();
        if (!is_array($body)) {
            EventLogger::warn('webhook', 'webhook_invalid_json', 'Invalid webhook JSON body', [
                'ip' => $ip,
            ]);
            return new WP_REST_Response(['code' => 'invalid_json', 'message' => 'Invalid JSON body'], 400);
        }

        $repo = new NotificationsRepository();
        $id = $repo->insert($body);
        if ($id <= 0) {
            EventLogger::error('webhook', 'webhook_insert_failed', 'Webhook insert failed', [
                'ip' => $ip,
                'source' => isset($body['source']) ? (string) $body['source'] : '',
                'type' => isset($body['type']) ? (string) $body['type'] : '',
            ]);
            return new WP_REST_Response(['code' => 'insert_failed', 'message' => 'Insert failed'], 400);
        }

        EventLogger::info('webhook', 'webhook_accepted', 'Webhook accepted', [
            'ip' => $ip,
            'notification_id' => $id,
            'source' => isset($body['source']) ? (string) $body['source'] : '',
            'type' => isset($body['type']) ? (string) $body['type'] : '',
        ]);

        return new WP_REST_Response(['success' => true, 'id' => $id], 201);
    }

    /**
     * @return true|WP_REST_Response
     */
    private function verifySignature(WP_REST_Request $request, string $ip) {
        $secret = trim((string) get_option('nh_webhook_secret', ''));
        if ($secret === '') {
            // Keep backwards compatibility when no secret is configured.
            return true;
        }

        $timestampRaw = trim((string) $request->get_header('x-nh-timestamp'));
        $signatureRaw = trim((string) $request->get_header('x-nh-signature'));

        if ($timestampRaw === '' || $signatureRaw === '') {
            EventLogger::warn('webhook', 'webhook_missing_signature', 'Missing webhook signature headers', [
                'ip' => $ip,
            ]);
            return new WP_REST_Response(['code' => 'missing_signature', 'message' => 'Missing signature headers'], 401);
        }

        if (!ctype_digit($timestampRaw)) {
            EventLogger::warn('webhook', 'webhook_bad_timestamp', 'Invalid webhook timestamp format', [
                'ip' => $ip,
                'timestamp' => $timestampRaw,
            ]);
            return new WP_REST_Response(['code' => 'invalid_timestamp', 'message' => 'Invalid timestamp'], 401);
        }

        $timestamp = (int) $timestampRaw;
        $window = (int) apply_filters('nh_webhook_hmac_window', self::HMAC_WINDOW_DEFAULT);
        if ($window <= 0) {
            $window = self::HMAC_WINDOW_DEFAULT;
        }

        if (abs(time() - $timestamp) > $window) {
            EventLogger::warn('webhook', 'webhook_timestamp_expired', 'Webhook timestamp outside accepted window', [
                'ip' => $ip,
                'timestamp' => $timestamp,
                'window' => $window,
            ]);
            return new WP_REST_Response(['code' => 'timestamp_expired', 'message' => 'Timestamp expired'], 401);
        }

        $sigParts = explode('=', $signatureRaw, 2);
        if (count($sigParts) !== 2 || strtolower((string) $sigParts[0]) !== 'sha256') {
            EventLogger::warn('webhook', 'webhook_bad_signature_format', 'Invalid webhook signature format', [
                'ip' => $ip,
            ]);
            return new WP_REST_Response(['code' => 'invalid_signature_format', 'message' => 'Invalid signature format'], 401);
        }

        $givenHex = strtolower(trim((string) $sigParts[1]));
        if ($givenHex === '' || !ctype_xdigit($givenHex)) {
            EventLogger::warn('webhook', 'webhook_bad_signature_hex', 'Invalid webhook signature hex', [
                'ip' => $ip,
            ]);
            return new WP_REST_Response(['code' => 'invalid_signature', 'message' => 'Invalid signature'], 401);
        }

        $rawBody = (string) $request->get_body();
        $signingString = $timestampRaw . '.' . $rawBody;
        $expectedHex = hash_hmac('sha256', $signingString, $secret);

        if (!hash_equals($expectedHex, $givenHex)) {
            EventLogger::warn('webhook', 'webhook_signature_mismatch', 'Webhook signature mismatch', [
                'ip' => $ip,
            ]);
            return new WP_REST_Response(['code' => 'invalid_signature', 'message' => 'Unauthorized'], 401);
        }

        $replayKey = 'nh_wh_replay_' . md5($timestampRaw . '|' . $givenHex);
        if (get_transient($replayKey) !== false) {
            EventLogger::warn('webhook', 'webhook_replay_detected', 'Webhook replay detected', [
                'ip' => $ip,
                'timestamp' => $timestamp,
            ]);
            return new WP_REST_Response(['code' => 'replay_detected', 'message' => 'Replay detected'], 401);
        }

        set_transient($replayKey, 1, $window);
        return true;
    }

    private function allowByRateLimit(string $ip): bool {
        $limit = (int) apply_filters('nh_webhook_rate_limit', self::RATE_LIMIT_DEFAULT);
        if ($limit <= 0) {
            $limit = self::RATE_LIMIT_DEFAULT;
        }

        $window = (int) apply_filters('nh_webhook_rate_window', self::RATE_WINDOW_DEFAULT);
        if ($window <= 0) {
            $window = self::RATE_WINDOW_DEFAULT;
        }

        $key = 'nh_rl_' . md5($ip);
        $count = (int) get_transient($key);
        if ($count >= $limit) {
            return false;
        }

        set_transient($key, $count + 1, $window);
        return true;
    }

    private function resolveClientIp(WP_REST_Request $request): string {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        $trustProxy = (bool) apply_filters('nh_webhook_trust_proxy', false);

        if ($trustProxy) {
            $xff = trim((string) $request->get_header('x-forwarded-for'));
            if ($xff !== '') {
                $parts = explode(',', $xff);
                $candidate = trim((string) ($parts[0] ?? ''));
                if ($this->isValidIp($candidate)) {
                    return $candidate;
                }
            }

            $real = trim((string) $request->get_header('x-real-ip'));
            if ($this->isValidIp($real)) {
                return $real;
            }
        }

        if ($this->isValidIp($ip)) {
            return $ip;
        }

        return '0.0.0.0';
    }

    private function isValidIp(string $ip): bool {
        if ($ip === '') {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
}
