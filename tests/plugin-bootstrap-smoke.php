<?php
/**
 * Plugin bootstrap smoke test (activation-like include with WP stubs).
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/plugin-bootstrap-smoke.php
 */

define('ABSPATH', __DIR__);
define('WP_DEBUG', false);

$GLOBALS['nh_actions'] = [];

function plugin_dir_path(string $file): string {
    return rtrim(str_replace('\\', '/', dirname($file)), '/') . '/';
}

function plugin_dir_url(string $file): string {
    return 'https://example.test/wp-content/plugins/' . basename(dirname($file)) . '/';
}

function plugin_basename(string $file): string {
    return basename(dirname($file)) . '/' . basename($file);
}

function load_plugin_textdomain(string $domain, bool $deprecated = false, string $relPath = ''): bool {
    return $domain === 'notification-hub' && $relPath !== '';
}

function add_action(string $hook, $callback, int $priority = 10, int $acceptedArgs = 1): void {
    $GLOBALS['nh_actions'][] = [$hook, $callback, $priority, $acceptedArgs];
}

require_once dirname(__DIR__) . '/notification-hub.php';

$checks = [
    'NH_VERSION constant' => defined('NH_VERSION') && NH_VERSION === '1.0.0',
    'NH_PLUGIN_DIR constant' => defined('NH_PLUGIN_DIR') && is_string(NH_PLUGIN_DIR),
    'nh_load_textdomain function' => function_exists('nh_load_textdomain'),
    'nh_boot function' => function_exists('nh_boot'),
    'plugins_loaded hook registered' => count(array_filter($GLOBALS['nh_actions'], static function ($row): bool {
        return isset($row[0]) && $row[0] === 'plugins_loaded';
    })) >= 2,
];

$allPass = true;
foreach ($checks as $label => $ok) {
    echo $label . ': ' . ($ok ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$ok) {
        $allPass = false;
    }
}

echo 'PLUGIN_BOOTSTRAP_SMOKE: ' . ($allPass ? 'PASS' : 'FAIL') . PHP_EOL;
if (!$allPass) {
    exit(1);
}
