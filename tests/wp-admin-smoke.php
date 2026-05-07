<?php
/**
 * WordPress admin-page smoke test for Notification Hub.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/wp-admin-smoke.php
 */

$wpLoad = 'E:/XAMPP/htdocs/MyProject/wp-load.php';
if (!file_exists($wpLoad)) {
    echo "WP_ADMIN_SMOKE: SKIP (wp-load.php not found)\n";
    exit(0);
}

require_once $wpLoad;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$legacyPlugin = WP_PLUGIN_DIR . '/Notification Hub/notification-hub.php';
$newPlugin = WP_PLUGIN_DIR . '/notification-hub/notification-hub.php';

if (!class_exists('NotificationHub\\Presenters\\Admin\\SettingsPage')) {
    if (file_exists($legacyPlugin)) {
        require_once $legacyPlugin;
    } elseif (file_exists($newPlugin)) {
        require_once $newPlugin;
    }
}

$admins = get_users([
    'role' => 'administrator',
    'number' => 1,
    'fields' => ['ID'],
]);

if (empty($admins) || empty($admins[0]->ID)) {
    echo "WP_ADMIN_SMOKE: SKIP (no admin user available)\n";
    exit(0);
}

wp_set_current_user((int) $admins[0]->ID);

$checks = [];

if (class_exists('NotificationHub\\Presenters\\Admin\\SettingsPage')) {
    ob_start();
    try {
        (new \NotificationHub\Presenters\Admin\SettingsPage())->render();
        $html = (string) ob_get_clean();
        $checks['Settings page render'] = $html !== '' && strpos($html, 'nh-settings') !== false;
        $checks['Settings nonce field'] = strpos($html, '_wpnonce') !== false;
    } catch (\Throwable $e) {
        ob_end_clean();
        $checks['Settings page render'] = false;
        $checks['Settings nonce field'] = false;
        $checks['Settings exception'] = false;
        echo 'Settings render exception: ' . $e->getMessage() . PHP_EOL;
    }
} else {
    $checks['Settings page class exists'] = false;
}

$allPass = true;
foreach ($checks as $label => $ok) {
    echo $label . ': ' . ($ok ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$ok) {
        $allPass = false;
    }
}

echo 'WP_ADMIN_SMOKE: ' . ($allPass ? 'PASS' : 'FAIL') . PHP_EOL;
if (!$allPass) {
    exit(1);
}
