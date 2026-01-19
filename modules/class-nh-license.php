<?php
/**
 * NH_License
 *
 * Central license policy + cached state for Pro features.
 *
 * @package Notification_Hub
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_License {

    public const OPT_KEY = 'nh_license_key';
    public const OPT_STATE = 'nh_license_state';
    public const OPT_VALID = 'nh_license_valid';

    /**
     * License server endpoint URL (verify.php).
     * Example: https://your-domain.com/license/verify.php
     *
     * @since 1.7.0
     */
    public const OPT_SERVER_URL = 'nh_license_server_url';

    /**
     * Enable extra debug logs in WP debug.log.
     *
     * @since 1.7.0
     */
    private const DEBUG = true;

    private const TRANSIENT_LOCK = 'nh_license_check_lock';

    public const GRACE_DAYS = 7;
    public const CHECK_TTL = 6 * HOUR_IN_SECONDS;

    /**
     * Strict key format.
     *
     * @since 1.7.0
     */
    private const KEY_REGEX = '/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/';

    /**
     * Capabilities that belong to Pro addon.
     *
     * @since 1.7.1
     */
    private const PRO_CAPS = ['telegram', 'slack'];

    public static function default_state(): array {
        return [
            'status'       => 'unknown',
            'features'     => [],
            'domain'       => '',
            'last_check'   => 0,
            'grace_until'  => 0,
            'message'      => '',
            'license_hash' => '',
        ];
    }

    /**
     * Pro addon presence flag.
     *
     * @since 1.7.1
     */
    public static function is_pro_addon_active(): bool {
        return defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE;
    }

    /** @since 1.7.0 */
    public static function get_server_url(): string {
        $url = get_option(self::OPT_SERVER_URL, '');
        $url = is_string($url) ? trim($url) : '';

        if ($url === '') {
            return '';
        }

        $url = esc_url_raw($url);
        return is_string($url) ? $url : '';
    }

    /** @since 1.6.2 */
    public static function get_key(): string {
        $key = get_option(self::OPT_KEY, '');
        $key = is_string($key) ? $key : '';
        $key = strtoupper(trim($key));
        return $key;
    }

    /** @since 1.6.2 */
    public static function save_key(string $key): void {
        $key = strtoupper(trim((string) $key));
        update_option(self::OPT_KEY, sanitize_text_field($key));
        self::reset_state();
    }

    /**
     * Validate license key format.
     *
     * @since 1.7.0
     */
    public static function is_valid_format(string $key): bool {
        $key = strtoupper(trim((string) $key));
        if ($key === '') {
            return false;
        }
        return (bool) preg_match(self::KEY_REGEX, $key);
    }

    /** @since 1.7.0 */
    public static function reset_state(): void {
        delete_option(self::OPT_STATE);
        update_option(self::OPT_VALID, false);
    }

    /** @since 1.7.0 */
    public static function get_state(): array {
        $state = get_option(self::OPT_STATE, []);
        if (!is_array($state)) {
            return self::default_state();
        }

        return array_merge(self::default_state(), $state);
    }

    /** @since 1.7.0 */
    public static function set_state(array $state): void {
        $state = array_merge(self::default_state(), $state);

        $state['features'] = is_array($state['features'])
            ? array_values(array_unique(array_map('strval', $state['features'])))
            : [];

        $state['last_check'] = (int) ($state['last_check'] ?? 0);
        $state['grace_until'] = (int) ($state['grace_until'] ?? 0);
        $state['domain'] = is_string($state['domain']) ? $state['domain'] : '';
        $state['status'] = is_string($state['status']) ? $state['status'] : 'unknown';
        $state['message'] = is_string($state['message']) ? $state['message'] : '';
        $state['license_hash'] = is_string($state['license_hash']) ? $state['license_hash'] : '';

        update_option(self::OPT_STATE, $state, false);
        update_option(self::OPT_VALID, self::is_active($state));
    }

    /** @since 1.7.0 */
    public static function is_pro(): bool {
        $state = self::get_state();
        return self::is_active($state) || self::is_in_grace($state);
    }

    /**
     * Central policy API.
     *
     * @since 1.7.0
     */
    public static function can(string $capability): bool {
        $capability = sanitize_key($capability);
        if ($capability === '') {
            return false;
        }

        // Pro capabilities require Pro addon presence.
        if (in_array($capability, self::PRO_CAPS, true) && !self::is_pro_addon_active()) {
            return false;
        }

        self::maybe_refresh();

        $state = self::get_state();
        if (!(self::is_active($state) || self::is_in_grace($state))) {
            return false;
        }

        $features = array_map('sanitize_key', (array) ($state['features'] ?? []));
        return in_array($capability, $features, true);
    }

    /**
     * Refresh cached state if TTL expired.
     *
     * @since 1.7.0
     */
    public static function maybe_refresh(): void {
        $state = self::get_state();
        $now = time();

        $key = self::get_key();
        if ($key === '') {
            return;
        }

        // Hard fail fast when key format is invalid.
        if (!self::is_valid_format($key)) {
            $state['status'] = 'inactive';
            $state['message'] = 'Invalid license key format.';
            $state['domain'] = self::get_current_domain();
            $state['license_hash'] = self::hash_key($key);
            $state['last_check'] = $now;
            $state['grace_until'] = 0;
            self::set_state($state);
            return;
        }

        if (self::get_server_url() === '') {
            if ((int) ($state['last_check'] ?? 0) === 0) {
                $state['status'] = 'inactive';
                $state['message'] = 'License server URL is not configured.';
                $state['domain'] = self::get_current_domain();
                $state['license_hash'] = self::hash_key($key);
                $state['last_check'] = $now;
                self::set_state($state);
            }
            return;
        }

        if ($state['last_check'] && ($now - (int) $state['last_check']) < self::CHECK_TTL) {
            return;
        }

        if (get_transient(self::TRANSIENT_LOCK)) {
            return;
        }

        set_transient(self::TRANSIENT_LOCK, 1, 30);

        $result = self::remote_verify();

        delete_transient(self::TRANSIENT_LOCK);

        if ($result['ok']) {
            self::set_state($result['state']);
            return;
        }

        $was_ok = self::is_active($state) || self::is_in_grace($state);
        if ($was_ok) {
            $state['status'] = 'grace';
            $state['message'] = $result['message'];
            $state['last_check'] = $now;
            $state['grace_until'] = max((int) $state['grace_until'], $now + (self::GRACE_DAYS * DAY_IN_SECONDS));
            self::set_state($state);
            return;
        }

        $state['status'] = 'inactive';
        $state['message'] = $result['message'];
        $state['last_check'] = $now;
        self::set_state($state);
    }

    /** @since 1.6.2 */
    public static function revoke(): void {
        delete_option(self::OPT_KEY);
        delete_option(self::OPT_VALID);
        delete_option(self::OPT_STATE);
        delete_option(self::OPT_SERVER_URL);
    }

    /**
     * A license is active when server says "active".
     *
     * @since 1.7.0
     */
    private static function is_active(array $state): bool {
        return isset($state['status']) && $state['status'] === 'active';
    }

    /** @since 1.7.0 */
    private static function is_in_grace(array $state): bool {
        $grace_until = (int) ($state['grace_until'] ?? 0);
        return $grace_until > 0 && time() <= $grace_until;
    }

    /** @since 1.7.0 */
    private static function log(string $msg, array $ctx = []): void {
        if (!self::DEBUG || !defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $line = '[NH_License] ' . $msg;
        if (!empty($ctx)) {
            $line .= ' ' . wp_json_encode($ctx);
        }

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log($line);
    }

    /**
     * Remote verification.
     *
     * @since 1.7.0
     */
    private static function remote_verify(): array {
        $key = self::get_key();
        $server_url = self::get_server_url();

        if ($key === '' || $server_url === '') {
            return [
                'ok' => false,
                'message' => 'License key or server URL missing.',
                'state' => self::default_state(),
            ];
        }

        if (!self::is_valid_format($key)) {
            return [
                'ok' => false,
                'message' => 'Invalid license key format.',
                'state' => self::default_state(),
            ];
        }

        $domain = self::get_current_domain();
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

        self::log('verify:POST', ['url' => $server_url, 'domain' => $domain]);

        $response = wp_remote_post($server_url, [
            'timeout' => 15,
            'redirection' => 5,
            'headers' => $headers,
            'body' => $payload,
        ]);

        // If blocked by WAF on POST, try GET as a fallback.
        $response_code = !is_wp_error($response) ? (int) wp_remote_retrieve_response_code($response) : 0;
        $content_type = !is_wp_error($response) ? (string) wp_remote_retrieve_header($response, 'content-type') : '';

        if (!is_wp_error($response) && $response_code === 403) {
            self::log('verify:post_403_try_get', ['url' => $server_url, 'domain' => $domain, 'content_type' => $content_type]);

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
                'state' => self::default_state(),
            ];
        }

        $raw = wp_remote_retrieve_body($response);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $code = (int) wp_remote_retrieve_response_code($response);
            $content_type = (string) wp_remote_retrieve_header($response, 'content-type');

            $snippet = substr((string) $raw, 0, 200);
            $snippet = preg_replace('/\s+/', ' ', (string) $snippet);

            $looks_like_js_challenge = (
                stripos($raw, 'aes.js') !== false
                || stripos($raw, 'toNumbers(') !== false
                || stripos($raw, 'anti-bot') !== false
                || stripos($raw, 'cf-browser-verification') !== false
            );

            self::log('verify:invalid_json', [
                'http' => $code,
                'content_type' => $content_type,
                'raw_prefix' => substr((string) $raw, 0, 200),
                'js_challenge' => $looks_like_js_challenge,
            ]);

            if ($looks_like_js_challenge) {
                return [
                    'ok' => false,
                    'message' => 'License server returned a JS anti-bot challenge page instead of JSON (this host blocks server-to-server requests). Move the license server to an API-friendly host or whitelist/disable the anti-bot protection for verify.php.',
                    'state' => self::default_state(),
                ];
            }

            $msg = 'Invalid JSON response from license server.';
            $msg .= ' HTTP ' . $code;
            if ($content_type !== '') {
                $msg .= ' (' . $content_type . ')';
            }
            if ($snippet !== '') {
                $msg .= ' First 200 chars: ' . $snippet;
            }

            // Hint for common hosting/WAF blocks.
            if ($code === 403) {
                $msg .= ' (Hint: hosting/WAF may be blocking server-to-server requests. Try allowing User-Agent: ' . $ua . ')';
            }

            return [
                'ok' => false,
                'message' => $msg,
                'state' => self::default_state(),
            ];
        }

        $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : 'inactive';
        $features = isset($data['features']) && is_array($data['features']) ? $data['features'] : [];
        $message = isset($data['message']) && is_string($data['message']) ? $data['message'] : '';
        $grace_days = isset($data['grace_days']) ? (int) $data['grace_days'] : self::GRACE_DAYS;

        // Accept all meaningful statuses from server.
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
            'license_hash' => self::hash_key($key),
        ];

        if ($status === 'grace') {
            $state['grace_until'] = time() + max(1, $grace_days) * DAY_IN_SECONDS;
        } else {
            $state['grace_until'] = 0;
        }

        self::log('verify:done', ['status' => $status, 'features' => $features]);

        // Only treat active/grace as OK.
        return [
            'ok' => ($status === 'active' || $status === 'grace'),
            'message' => $message !== '' ? $message : 'License server response.',
            'state' => $state,
        ];
    }

    /** @since 1.7.0 */
    public static function get_current_domain(): string {
        $home = home_url();
        $host = wp_parse_url($home, PHP_URL_HOST);
        return is_string($host) ? strtolower($host) : '';
    }

    /** @since 1.7.0 */
    public static function hash_key(string $key): string {
        $key = trim($key);
        if ($key === '') {
            return '';
        }

        return hash_hmac('sha256', $key, wp_salt('auth'));
    }
}
