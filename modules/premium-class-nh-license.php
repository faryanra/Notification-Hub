<?php
/**
 * NH_License
 *
 * Central license policy + cached state for Premium features.
 *
 * NOTE: This file is premium-prefixed so it can be extracted into the Premium ZIP.
 * Class name stays unchanged for backward compatibility.
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
     * NOTE: Turned off by default to avoid noisy logs on production.
     *
     * @since 1.7.1
     */
    private const DEBUG = false;

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
     * Capabilities that belong to Premium addon.
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
     * Premium addon presence flag.
     *
     * @since 1.7.1
     */
    public static function is_pro_addon_active(): bool {
        return defined('NH_PRO_ACTIVE') && (bool) NH_PRO_ACTIVE;
    }

    /**
     * Check compatibility between Free and Premium.
     *
     * If Premium defines NH_PRO_VERSION, enforce exact match with NH_VERSION.
     *
     * @since 1.7.1
     */
    public static function is_pro_version_compatible(): bool {
        if (!self::is_pro_addon_active()) {
            return false;
        }

        if (!defined('NH_VERSION')) {
            return true;
        }

        if (!defined('NH_PRO_VERSION')) {
            return true;
        }

        return (string) NH_PRO_VERSION === (string) NH_VERSION;
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

    /**
     * Persist normalized state.
     *
     * NOTE: This method is required by maybe_refresh().
     *
     * @since 1.7.0
     */
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

    /**
     * Human hints for common statuses.
     *
     * @since 1.7.1
     */
    public static function status_hint(array $state = null): string {
        $state = is_array($state) ? $state : self::get_state();
        $status = isset($state['status']) ? (string) $state['status'] : 'unknown';

        switch ($status) {
            case 'active':
                return 'License is active.';

            case 'grace':
                return 'License check failed temporarily. Premium remains enabled during the grace window. Check your server/WAF logs.';

            case 'expired':
                return 'License is expired. Renew your subscription and save the updated license key.';

            case 'revoked':
                return 'License was revoked. Revoke locally and enter a new valid license key.';

            case 'banned':
                return 'License is banned. Contact support.';

            case 'inactive':
                $msg = isset($state['message']) ? (string) $state['message'] : '';
                if (stripos($msg, 'anti-bot') !== false || stripos($msg, 'cloudflare') !== false) {
                    return 'Your license endpoint may be blocked by Cloudflare/WAF. Allowlist the verify.php path and disable challenges for it.';
                }

                if (stripos($msg, 'domain') !== false) {
                    return 'Possible domain mismatch. Ensure the license is issued for this site domain and re-verify.';
                }

                return 'License is inactive. Verify server URL and key, then try again.';

            default:
                return 'License status is unknown. Save server URL and key, then refresh.';
        }
    }

    /**
     * Pro / Premium flag.
     *
     * @since 1.7.0
     */
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

        // Premium capabilities require Premium addon presence + version compatibility.
        if (in_array($capability, self::PRO_CAPS, true)) {
            if (!self::is_pro_addon_active()) {
                return false;
            }

            if (!self::is_pro_version_compatible()) {
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

        if ($state['last_check'] && ($now - (int) $state['last_check']) < self::CHECK_TTL) {
            return;
        }

        if (get_transient(self::TRANSIENT_LOCK)) {
            return;
        }

        set_transient(self::TRANSIENT_LOCK, 1, 30);

        if (!class_exists('NH_License_Client')) {
            $client = NH_PLUGIN_DIR . 'modules/license/class-nh-license-client.php';
            if (file_exists($client)) {
                require_once $client;
            }
        }

        $result = class_exists('NH_License_Client')
            ? NH_License_Client::remote_verify($key, $server_url, self::DEBUG)
            : [
                'ok' => false,
                'message' => 'License client missing.',
                'state' => self::default_state(),
            ];

        delete_transient(self::TRANSIENT_LOCK);

        if ($result['ok']) {
            self::set_state(is_array($result['state']) ? $result['state'] : []);
            return;
        }

        $was_ok = self::is_active($state) || self::is_in_grace($state);
        if ($was_ok) {
            $state['status'] = 'grace';
            $state['message'] = (string) ($result['message'] ?? '');
            $state['last_check'] = $now;
            $state['grace_until'] = max((int) $state['grace_until'], $now + (self::GRACE_DAYS * DAY_IN_SECONDS));
            self::set_state($state);
            return;
        }

        $state['status'] = 'inactive';
        $state['message'] = (string) ($result['message'] ?? '');
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