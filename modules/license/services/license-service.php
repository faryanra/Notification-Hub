<?php
/**
 * License service.
 *
 * Orchestrates license operations.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Service {

    /**
     * Option keys (kept in sync with NH_License facade).
     *
     * @since 1.7.2
     */
    public const OPT_KEY        = 'nh_license_key';
    public const OPT_STATE      = 'nh_license_state';
    public const OPT_VALID      = 'nh_license_valid';
    public const OPT_SERVER_URL = 'nh_license_server_url';

    /**
     * Grace window (days).
     *
     * @since 1.7.2
     */
    public const GRACE_DAYS = 7;

    /**
     * Check TTL.
     *
     * @since 1.7.2
     */
    public const CHECK_TTL = 6 * HOUR_IN_SECONDS;

    /**
     * Lock key.
     *
     * @since 1.7.2
     */
    private const TRANSIENT_LOCK = 'nh_license_check_lock';

    /**
     * Enable extra debug logs.
     *
     * @since 1.7.2
     */
    private const DEBUG = false;

    /**
     * Default state.
     *
     * @since 1.7.2
     * @return array<string,mixed>
     */
    public function default_state(): array {
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
     * Read normalized state.
     *
     * @since 1.7.2
     * @return array<string,mixed>
     */
    public function read_state(): array {
        $state = $this->opt_get(self::OPT_STATE, []);
        if (!is_array($state)) {
            return $this->default_state();
        }

        return array_merge($this->default_state(), $state);
    }

    /**
     * Write normalized state.
     *
     * @since 1.7.2
     * @param array<string,mixed> $state State.
     * @return void
     */
    public function write_state(array $state): void {
        $state = array_merge($this->default_state(), $state);

        $state['features'] = is_array($state['features'])
            ? array_values(array_unique(array_map('strval', $state['features'])))
            : [];

        $state['last_check'] = (int) ($state['last_check'] ?? 0);
        $state['grace_until'] = (int) ($state['grace_until'] ?? 0);
        $state['domain'] = is_string($state['domain']) ? $state['domain'] : '';
        $state['status'] = is_string($state['status']) ? $state['status'] : 'unknown';
        $state['message'] = is_string($state['message']) ? $state['message'] : '';
        $state['license_hash'] = is_string($state['license_hash']) ? $state['license_hash'] : '';

        $this->opt_set(self::OPT_STATE, $state, false);
        $this->opt_set(self::OPT_VALID, $this->is_active($state));
    }

    /**
     * Refresh state if needed.
     *
     * @since 1.7.2
     * @return void
     */
    public function maybe_refresh(): void {
        $state = $this->read_state();
        $now   = time();

        $key = $this->get_key();
        if ($key === '') {
            return;
        }

        // Hard fail fast when key format is invalid.
        if (!$this->validate_key_format($key)) {
            $state['status'] = 'inactive';
            $state['message'] = 'Invalid license key format.';
            $state['domain'] = $this->get_current_domain();
            $state['license_hash'] = $this->hash_key($key);
            $state['last_check'] = $now;
            $state['grace_until'] = 0;
            $this->write_state($state);
            return;
        }

        $server_url = $this->get_server_url();
        if ($server_url === '') {
            if ((int) ($state['last_check'] ?? 0) === 0) {
                $state['status'] = 'inactive';
                $state['message'] = 'License server URL is not configured.';
                $state['domain'] = $this->get_current_domain();
                $state['license_hash'] = $this->hash_key($key);
                $state['last_check'] = $now;
                $this->write_state($state);
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

        $result = $this->remote_verify($key, $server_url);

        delete_transient(self::TRANSIENT_LOCK);

        if (!empty($result['ok'])) {
            $this->write_state(is_array($result['state'] ?? null) ? (array) $result['state'] : []);
            return;
        }

        $was_ok = $this->is_active($state) || $this->is_in_grace($state);
        if ($was_ok) {
            $state['status'] = 'grace';
            $state['message'] = (string) ($result['message'] ?? '');
            $state['last_check'] = $now;
            $state['grace_until'] = max((int) ($state['grace_until'] ?? 0), $now + (self::GRACE_DAYS * DAY_IN_SECONDS));
            $this->write_state($state);
            return;
        }

        $state['status'] = 'inactive';
        $state['message'] = (string) ($result['message'] ?? '');
        $state['last_check'] = $now;
        $this->write_state($state);
    }

    /**
     * Refresh now.
     *
     * @since 1.7.2
     * @return void
     */
    public function refresh_now(): void {
        $state = $this->read_state();
        $state['last_check'] = 0;
        $this->write_state($state);
        $this->maybe_refresh();
    }

    /**
     * Revoke license.
     *
     * @since 1.7.2
     * @return void
     */
    public function revoke(): void {
        $this->opt_delete(self::OPT_KEY);
        $this->opt_delete(self::OPT_VALID);
        $this->opt_delete(self::OPT_STATE);
        $this->opt_delete(self::OPT_SERVER_URL);
    }

    /**
     * Get license key.
     *
     * @since 1.7.2
     * @return string
     */
    public function get_key(): string {
        $key = $this->opt_get(self::OPT_KEY, '');
        $key = is_string($key) ? $key : '';
        return strtoupper(trim($key));
    }

    /**
     * Get server URL.
     *
     * @since 1.7.2
     * @return string
     */
    public function get_server_url(): string {
        $url = $this->opt_get(self::OPT_SERVER_URL, '');
        $url = is_string($url) ? trim($url) : '';

        if ($url === '') {
            return '';
        }

        $url = esc_url_raw($url);
        return is_string($url) ? $url : '';
    }

    /**
     * Validate license key format.
     *
     * @since 1.7.2
     * @param string $key License key.
     * @return bool
     */
    protected function validate_key_format(string $key): bool {
        if (!class_exists('NH_License_Key_Format')) {
            $path = NH_PLUGIN_DIR . 'modules/license/policy/key-format.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_Key_Format')) {
            return (bool) NH_License_Key_Format::validate_key_format($key);
        }

        // Fallback.
        return (bool) preg_match('/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/', strtoupper(trim($key)));
    }

    /**
     * Remote verify via HTTP client wrapper.
     *
     * @since 1.7.2
     * @param string $key Key.
     * @param string $server_url URL.
     * @return array<string,mixed>
     */
    protected function remote_verify(string $key, string $server_url): array {
        if (!class_exists('NH_License_HTTP_Client')) {
            $path = NH_PLUGIN_DIR . 'modules/license/http/license-client.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_HTTP_Client')) {
            return (array) NH_License_HTTP_Client::remote_verify($key, $server_url, self::DEBUG);
        }

        return [
            'ok'      => false,
            'message' => 'License client missing.',
            'state'   => $this->default_state(),
        ];
    }

    /**
     * A license is active when server says "active".
     *
     * @since 1.7.2
     * @param array<string,mixed> $state State.
     * @return bool
     */
    protected function is_active(array $state): bool {
        return isset($state['status']) && $state['status'] === 'active';
    }

    /**
     * Check if still in grace.
     *
     * @since 1.7.2
     * @param array<string,mixed> $state State.
     * @return bool
     */
    protected function is_in_grace(array $state): bool {
        $grace_until = (int) ($state['grace_until'] ?? 0);
        return $grace_until > 0 && time() <= $grace_until;
    }

    /**
     * Current domain.
     *
     * @since 1.7.2
     * @return string
     */
    protected function get_current_domain(): string {
        $home = home_url();
        $host = wp_parse_url($home, PHP_URL_HOST);
        return is_string($host) ? strtolower($host) : '';
    }

    /**
     * Hash key.
     *
     * @since 1.7.2
     * @param string $key Key.
     * @return string
     */
    protected function hash_key(string $key): string {
        $key = trim($key);
        if ($key === '') {
            return '';
        }

        return hash_hmac('sha256', $key, wp_salt('auth'));
    }

    /**
     * Option get.
     *
     * @since 1.7.2
     * @param string $key Key.
     * @param mixed  $default Default.
     * @return mixed
     */
    protected function opt_get(string $key, $default = null) {
        if (!class_exists('NH_License_Option_Store')) {
            $path = NH_PLUGIN_DIR . 'modules/license/storage/option-store.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_Option_Store')) {
            return NH_License_Option_Store::get($key, $default);
        }

        return get_option($key, $default);
    }

    /**
     * Option set.
     *
     * @since 1.7.2
     * @param string $key Key.
     * @param mixed  $value Value.
     * @param bool   $autoload Autoload.
     * @return bool
     */
    protected function opt_set(string $key, $value, bool $autoload = false): bool {
        if (!class_exists('NH_License_Option_Store')) {
            $path = NH_PLUGIN_DIR . 'modules/license/storage/option-store.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_Option_Store')) {
            return (bool) NH_License_Option_Store::set($key, $value, $autoload);
        }

        return (bool) update_option($key, $value, $autoload);
    }

    /**
     * Option delete.
     *
     * @since 1.7.2
     * @param string $key Key.
     * @return bool
     */
    protected function opt_delete(string $key): bool {
        if (!class_exists('NH_License_Option_Store')) {
            $path = NH_PLUGIN_DIR . 'modules/license/storage/option-store.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (class_exists('NH_License_Option_Store')) {
            return (bool) NH_License_Option_Store::delete($key);
        }

        return (bool) delete_option($key);
    }
}
