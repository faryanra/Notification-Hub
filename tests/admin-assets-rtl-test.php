<?php
/**
 * RTL enqueue smoke test for admin assets.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/admin-assets-rtl-test.php
 */

namespace NotificationHub\Integrations {
    interface Integration {
        public function register(\NotificationHub\Loader $loader): void;
    }
}

namespace NotificationHub {
    final class Loader {
        public function addAction(string $hook, array $callback, int $priority = 10, int $acceptedArgs = 1): void {
        }
    }
}

namespace {
    define('ABSPATH', __DIR__);
    define('NH_PLUGIN_URL', 'https://example.test/wp-content/plugins/notification-hub/');
    define('NH_PLUGIN_DIR', dirname(__DIR__) . '/');
    define('NH_VERSION', '1.0.0');

    $GLOBALS['nh_styles'] = [];
    $GLOBALS['nh_scripts'] = [];
    $GLOBALS['nh_test_is_rtl'] = false;

    function is_rtl(): bool {
        return (bool) $GLOBALS['nh_test_is_rtl'];
    }

    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], $ver = false, $inFooter = false): void {
        $GLOBALS['nh_scripts'][$handle] = compact('src', 'deps', 'ver', 'inFooter');
    }

    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], $ver = false, $media = 'all'): void {
        $GLOBALS['nh_styles'][$handle] = compact('src', 'deps', 'ver', 'media');
    }

    function wp_localize_script(string $handle, string $name, array $data): void {
    }

    function admin_url(string $path = ''): string {
        return 'https://example.test/wp-admin/' . ltrim($path, '/');
    }

    function wp_create_nonce(string $action): string {
        return 'nonce-' . $action;
    }

    function esc_html__(string $text, string $domain = ''): string {
        return $text;
    }

    function esc_url_raw(string $url): string {
        return $url;
    }

    function trailingslashit(string $value): string {
        return rtrim($value, '/') . '/';
    }

    function get_rest_url($blog = null, string $path = ''): string {
        return 'https://example.test/wp-json/' . ltrim($path, '/');
    }

    function current_time(string $type): string {
        return '2026-05-02 12:00:00';
    }

    function wp_style_is(string $handle, string $list = 'enqueued'): bool {
        return isset($GLOBALS['nh_styles'][$handle]);
    }

    require_once __DIR__ . '/../src/Integrations/Admin/AdminAssets.php';

    $assets = new \NotificationHub\Integrations\Admin\AdminAssets();

    $GLOBALS['nh_test_is_rtl'] = false;
    $GLOBALS['nh_styles'] = [];
    $GLOBALS['nh_scripts'] = [];
    $assets->enqueue('toplevel_page_nh-dashboard');
    $passLtr = isset($GLOBALS['nh_styles']['nh-admin']) && !isset($GLOBALS['nh_styles']['nh-admin-rtl']);

    $GLOBALS['nh_test_is_rtl'] = true;
    $GLOBALS['nh_styles'] = [];
    $GLOBALS['nh_scripts'] = [];
    $assets->enqueue('toplevel_page_nh-dashboard');
    $passRtl = isset($GLOBALS['nh_styles']['nh-admin']) && isset($GLOBALS['nh_styles']['nh-admin-rtl']);

    echo 'RTL_ENQUEUE_LTR_CASE: ' . ($passLtr ? 'PASS' : 'FAIL') . PHP_EOL;
    echo 'RTL_ENQUEUE_RTL_CASE: ' . ($passRtl ? 'PASS' : 'FAIL') . PHP_EOL;

    if (!$passLtr || !$passRtl) {
        exit(1);
    }
}
