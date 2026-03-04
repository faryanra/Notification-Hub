<?php
/**
 * Legacy-compatible premium license facade.
 *
 * Provides the historic NH_License API without depending on legacy modules/.
 *
 * @since 1.7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NH_License')) {
    class NH_License {
        public const OPT_KEY = 'nh_license_key';
        public const OPT_STATE = 'nh_license_state';
        public const OPT_VALID = 'nh_license_valid';
        public const OPT_SERVER_URL = 'nh_license_server_url';

        private const TRANSIENT_LOCK = 'nh_license_check_lock';
        private const DEBUG = false;
        private const KEY_REGEX = '/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/';
        private const PRO_CAPS = ['telegram', 'slack'];

        public const GRACE_DAYS = 7;
        public const CHECK_TTL = 6 * HOUR_IN_SECONDS;

        /**
         * @return array<string,mixed>
         */
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

        public static function is_pro_addon_active(): bool {
            return defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE;
        }

        public static function is_pro_version_compatible(): bool {
            if (!self::is_pro_addon_active()) {
                return false;
            }

            if (!defined('NH_VERSION') || !defined('NH_PRO_VERSION')) {
                return true;
            }

            return (string) NH_PRO_VERSION === (string) NH_VERSION;
        }

        public static function get_server_url(): string {
            $url = get_option(self::OPT_SERVER_URL, '');
            $url = is_string($url) ? trim($url) : '';
            if ($url === '') {
                return '';
            }

            $url = esc_url_raw($url);
            return is_string($url) ? $url : '';
        }

        public static function get_key(): string {
            $key = get_option(self::OPT_KEY, '');
            $key = is_string($key) ? $key : '';
            return strtoupper(trim($key));
        }

        public static function save_key(string $key): void {
            $key = strtoupper(trim($key));
            update_option(self::OPT_KEY, sanitize_text_field($key), false);
            self::reset_state();
        }

        public static function is_valid_format(string $key): bool {
            $key = strtoupper(trim($key));
            if ($key === '') {
                return false;
            }

            return (bool) preg_match(self::KEY_REGEX, $key);
        }

        public static function reset_state(): void {
            delete_option(self::OPT_STATE);
            update_option(self::OPT_VALID, false, false);
        }

        public static function set_valid(bool $valid): void {
            update_option(self::OPT_VALID, (bool) $valid, false);
        }

        /**
         * @return array<string,mixed>
         */
        public static function get_state(): array {
            $state = get_option(self::OPT_STATE, []);
            if (!is_array($state)) {
                return self::default_state();
            }

            return array_merge(self::default_state(), $state);
        }

        /**
         * @param array<string,mixed> $state
         */
        public static function set_state(array $state): void {
            $state = array_merge(self::default_state(), $state);

            $state['features'] = is_array($state['features'])
                ? array_values(array_unique(array_map('sanitize_key', $state['features'])))
                : [];

            $state['last_check'] = (int) ($state['last_check'] ?? 0);
            $state['grace_until'] = (int) ($state['grace_until'] ?? 0);
            $state['domain'] = is_string($state['domain']) ? $state['domain'] : '';
            $state['status'] = is_string($state['status']) ? $state['status'] : 'unknown';
            $state['message'] = is_string($state['message']) ? $state['message'] : '';
            $state['license_hash'] = is_string($state['license_hash']) ? $state['license_hash'] : '';

            update_option(self::OPT_STATE, $state, false);
            update_option(self::OPT_VALID, self::is_active($state), false);
        }

        /**
         * @param array<string,mixed>|null $state
         */
        public static function status_hint(array $state = null): string {
            $state = is_array($state) ? $state : self::get_state();
            $status = isset($state['status']) ? (string) $state['status'] : 'unknown';

            switch ($status) {
                case 'active':
                    return 'License is active.';
                case 'grace':
                    return 'License check failed temporarily. Premium remains enabled during grace.';
                case 'expired':
                    return 'License expired. Renew and save a valid key.';
                case 'revoked':
                    return 'License revoked. Revoke locally and enter a valid key.';
                case 'banned':
                    return 'License is banned. Contact support.';
                case 'inactive':
                    return 'License is inactive. Verify server URL and key.';
                default:
                    return 'License status unknown. Save server URL and key, then refresh.';
            }
        }

        public static function is_pro(): bool {
            $state = self::get_state();
            return self::is_active($state) || self::is_in_grace($state);
        }

        public static function can(string $capability): bool {
            $capability = sanitize_key($capability);
            if ($capability === '') {
                return false;
            }

            if (in_array($capability, self::PRO_CAPS, true)) {
                if (!self::is_pro_addon_active() || !self::is_pro_version_compatible()) {
                    return false;
                }
            }

            self::maybe_refresh();
            $state = self::get_state();

            if (!(self::is_active($state) || self::is_in_grace($state))) {
                return false;
            }

            $features = array_map('sanitize_key', (array) ($state['features'] ?? []));
            return in_array($capability, $features, true);
        }

        public static function maybe_refresh(): void {
            $state = self::get_state();
            $now = time();
            $key = self::get_key();

            if ($key === '') {
                return;
            }

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

            $server_url = self::get_server_url();
            if ($server_url === '') {
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

            if (!empty($state['last_check']) && ($now - (int) $state['last_check']) < self::CHECK_TTL) {
                return;
            }

            if (get_transient(self::TRANSIENT_LOCK)) {
                return;
            }

            set_transient(self::TRANSIENT_LOCK, 1, 30);
            $result = self::remote_verify($key, $server_url);
            delete_transient(self::TRANSIENT_LOCK);

            if (!empty($result['ok'])) {
                $new_state = is_array($result['state'] ?? null) ? (array) $result['state'] : self::default_state();
                self::set_state($new_state);
                return;
            }

            $was_ok = self::is_active($state) || self::is_in_grace($state);
            if ($was_ok) {
                $state['status'] = 'grace';
                $state['message'] = (string) ($result['message'] ?? '');
                $state['last_check'] = $now;
                $state['grace_until'] = max((int) ($state['grace_until'] ?? 0), $now + (self::GRACE_DAYS * DAY_IN_SECONDS));
                self::set_state($state);
                return;
            }

            $state['status'] = 'inactive';
            $state['message'] = (string) ($result['message'] ?? '');
            $state['last_check'] = $now;
            self::set_state($state);
        }

        public static function revoke(): void {
            delete_option(self::OPT_KEY);
            delete_option(self::OPT_VALID);
            delete_option(self::OPT_STATE);
            delete_option(self::OPT_SERVER_URL);
        }

        public static function get_current_domain(): string {
            $home = home_url();
            $host = wp_parse_url($home, PHP_URL_HOST);
            return is_string($host) ? strtolower($host) : '';
        }

        public static function hash_key(string $key): string {
            $key = trim($key);
            if ($key === '') {
                return '';
            }

            return hash_hmac('sha256', $key, wp_salt('auth'));
        }

        /**
         * @return array<string,mixed>
         */
        private static function remote_verify(string $key, string $server_url): array {
            $domain = self::get_current_domain();
            $site_id = md5($domain . '|' . wp_salt('auth'));

            $payload = [
                'product'     => 'notification-hub',
                'license_key' => $key,
                'domain'      => $domain,
                'site_id'     => $site_id,
            ];

            $ua = 'NotificationHub/' . (defined('NH_VERSION') ? NH_VERSION : 'dev') . '; ' . home_url('/');

            $response = wp_remote_post($server_url, [
                'timeout'     => 15,
                'redirection' => 5,
                'headers'     => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'User-Agent'   => $ua,
                ],
                'body'        => $payload,
            ]);

            $response_code = !is_wp_error($response) ? (int) wp_remote_retrieve_response_code($response) : 0;
            if (!is_wp_error($response) && $response_code === 403) {
                $response = wp_remote_get(add_query_arg($payload, $server_url), [
                    'timeout'     => 15,
                    'redirection' => 5,
                    'headers'     => [
                        'Accept'     => 'application/json',
                        'User-Agent' => $ua,
                    ],
                ]);
            }

            if (is_wp_error($response)) {
                return [
                    'ok'      => false,
                    'message' => $response->get_error_message(),
                    'state'   => [],
                ];
            }

            $raw = (string) wp_remote_retrieve_body($response);
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                $code = (int) wp_remote_retrieve_response_code($response);
                $content_type = (string) wp_remote_retrieve_header($response, 'content-type');
                $snippet = substr((string) $raw, 0, 200);
                $snippet = preg_replace('/\s+/', ' ', (string) $snippet);

                $msg = 'Invalid JSON response from license server. HTTP ' . $code;
                if ($content_type !== '') {
                    $msg .= ' (' . $content_type . ')';
                }
                if ($snippet !== '') {
                    $msg .= ' First 200 chars: ' . $snippet;
                }

                if (self::DEBUG && defined('WP_DEBUG') && WP_DEBUG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log('[NH_License] verify invalid_json: ' . $msg);
                }

                return [
                    'ok'      => false,
                    'message' => $msg,
                    'state'   => [],
                ];
            }

            $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : 'inactive';
            $features = isset($data['features']) && is_array($data['features']) ? $data['features'] : [];
            $message = isset($data['message']) && is_string($data['message']) ? $data['message'] : '';
            $grace_days = isset($data['grace_days']) ? (int) $data['grace_days'] : self::GRACE_DAYS;

            $allowed = ['active', 'inactive', 'revoked', 'grace', 'banned', 'expired'];
            if (!in_array($status, $allowed, true)) {
                $status = 'inactive';
            }

            $state = [
                'status'       => $status,
                'features'     => $features,
                'domain'       => $domain,
                'last_check'   => time(),
                'message'      => $message,
                'license_hash' => self::hash_key($key),
                'grace_until'  => ($status === 'grace') ? (time() + max(1, $grace_days) * DAY_IN_SECONDS) : 0,
            ];

            return [
                'ok'      => ($status === 'active' || $status === 'grace'),
                'message' => $message !== '' ? $message : 'License server response.',
                'state'   => $state,
            ];
        }

        /**
         * @param array<string,mixed> $state
         */
        private static function is_active(array $state): bool {
            return isset($state['status']) && $state['status'] === 'active';
        }

        /**
         * @param array<string,mixed> $state
         */
        private static function is_in_grace(array $state): bool {
            $grace_until = (int) ($state['grace_until'] ?? 0);
            return $grace_until > 0 && time() <= $grace_until;
        }
    }
}

