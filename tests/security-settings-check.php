<?php
/**
 * Static security checks for settings and channel save paths.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/security-settings-check.php
 */

$base = dirname(__DIR__);

function file_contains(string $path, string $needle): bool {
    $content = file_get_contents($path);
    return $content !== false && strpos($content, $needle) !== false;
}

$checks = [
    [
        'name' => 'Settings page capability check',
        'ok' => file_contains($base . '/src/Presenters/Admin/SettingsPage.php', "current_user_can('manage_options')"),
    ],
    [
        'name' => 'General settings nonce field',
        'ok' => file_contains($base . '/templates/settings/page.php', "settings_fields('nh_settings_general')"),
    ],
    [
        'name' => 'Channel settings nonce field',
        'ok' => file_contains($base . '/templates/settings/partials/tab-channels.php', "settings_fields('nh_settings_channels')"),
    ],
    [
        'name' => 'Test channel capability check',
        'ok' => file_contains($base . '/src/Routes/Admin/TestChannel.php', "current_user_can('manage_options')"),
    ],
    [
        'name' => 'Test channel nonce verification',
        'ok' => file_contains($base . '/src/Routes/Admin/TestChannel.php', "check_admin_referer('nh_test_channel')"),
    ],
    [
        'name' => 'Email sanitized with sanitize_email',
        'ok' => file_contains($base . '/src/Integrations/Admin/SettingsRegistration.php', 'sanitize_email'),
    ],
    [
        'name' => 'Slack webhook sanitized with esc_url_raw',
        'ok' => file_contains($base . '/src/Integrations/Admin/SettingsRegistration.php', 'esc_url_raw'),
    ],
    [
        'name' => 'Boolean sanitized with rest_sanitize_boolean',
        'ok' => file_contains($base . '/src/Integrations/Admin/SettingsRegistration.php', 'rest_sanitize_boolean'),
    ],
    [
        'name' => 'REST boolean sanitized with rest_sanitize_boolean',
        'ok' => file_contains($base . '/src/Integrations/Api/RestRoutesRegistration.php', "'sanitize_callback' => 'rest_sanitize_boolean'"),
    ],
    [
        'name' => 'Telegram token sanitized before saving',
        'ok' => file_contains($base . '/src/Repositories/SettingsRepository.php', "update_option(self::OPT_TELEGRAM_BOT_TOKEN, sanitize_text_field"),
    ],
    [
        'name' => 'Telegram chat ID sanitized before saving',
        'ok' => file_contains($base . '/src/Repositories/SettingsRepository.php', "update_option(self::OPT_TELEGRAM_CHAT_ID, sanitize_text_field"),
    ],
    [
        'name' => 'Slack webhook sanitized before saving',
        'ok' => file_contains($base . '/src/Repositories/SettingsRepository.php', "update_option(self::OPT_SLACK_WEBHOOK, esc_url_raw"),
    ],
];

$allPass = true;
foreach ($checks as $check) {
    echo $check['name'] . ': ' . ($check['ok'] ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$check['ok']) {
        $allPass = false;
    }
}

echo 'SECURITY_SETTINGS_CHECK: ' . ($allPass ? 'PASS' : 'FAIL') . PHP_EOL;
if (!$allPass) {
    exit(1);
}
