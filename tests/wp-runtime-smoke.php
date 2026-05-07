<?php
/**
 * WordPress runtime smoke checks (local environment).
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/wp-runtime-smoke.php
 */

$wpLoad = 'E:/XAMPP/htdocs/MyProject/wp-load.php';
if (!file_exists($wpLoad)) {
    echo "WP_RUNTIME_SMOKE: SKIP (wp-load.php not found)\n";
    exit(0);
}

require_once $wpLoad;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$slug = 'notification-hub/notification-hub.php';
$pluginFile = WP_PLUGIN_DIR . '/' . $slug;

if (!file_exists($pluginFile)) {
    echo "WP_RUNTIME_PLUGIN_FILE: FAIL\n";
    echo "WP_RUNTIME_SMOKE: FAIL\n";
    exit(1);
}

$header = get_plugin_data($pluginFile, false, false);
$headerOk = ($header['Version'] ?? '') === '1.0.0' && ($header['TextDomain'] ?? '') === 'notification-hub';

$activationStatus = 'SKIP';
$activationOk = true;
$isActiveAfter = true;
$activationError = '';

if (function_exists('nh_load_textdomain')) {
    $activationStatus = 'SKIP (legacy folder already loaded in runtime)';
} else {
    $beforeActive = get_option('active_plugins', []);
    $activationResult = activate_plugin($slug, '', false, true);
    $activationOk = !is_wp_error($activationResult);
    $isActiveAfter = is_plugin_active($slug);

    if (!$activationOk && is_wp_error($activationResult)) {
        $activationError = $activationResult->get_error_message();
    }

    // Restore plugin activation state to avoid side effects.
    update_option('active_plugins', $beforeActive);
    $activationStatus = $activationOk ? 'PASS' : 'FAIL';
}

echo 'WP_RUNTIME_PLUGIN_FILE: PASS' . PHP_EOL;
echo 'WP_RUNTIME_HEADER: ' . ($headerOk ? 'PASS' : 'FAIL') . PHP_EOL;
echo 'WP_RUNTIME_ACTIVATION_CALL: ' . $activationStatus . PHP_EOL;
if ($activationStatus !== 'SKIP (legacy folder already loaded in runtime)') {
    echo 'WP_RUNTIME_IS_ACTIVE_AFTER_ACTIVATE: ' . ($isActiveAfter ? 'PASS' : 'FAIL') . PHP_EOL;
}

$allPass = $headerOk && $activationOk && $isActiveAfter;
echo 'WP_RUNTIME_SMOKE: ' . ($allPass ? 'PASS' : 'FAIL') . PHP_EOL;
if (!$allPass) {
    if ($activationError !== '') {
        echo 'Activation error: ' . $activationError . PHP_EOL;
    }
    exit(1);
}
