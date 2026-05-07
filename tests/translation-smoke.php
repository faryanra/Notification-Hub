<?php
/**
 * Translation smoke checks for Notification Hub locales.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/translation-smoke.php
 */

$base = dirname(__DIR__);
$files = [
    $base . '/languages/notification-hub.pot',
    $base . '/languages/notification-hub-fa_IR.po',
    $base . '/languages/notification-hub-fa_IR.mo',
    $base . '/languages/notification-hub-it_IT.po',
    $base . '/languages/notification-hub-it_IT.mo',
];

$allPass = true;
foreach ($files as $file) {
    $ok = is_file($file) && filesize($file) > 0;
    echo basename($file) . ': ' . ($ok ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$ok) {
        $allPass = false;
    }
}

$mainFile = $base . '/notification-hub.php';
$mainContent = file_get_contents($mainFile) ?: '';
$textdomainLoaded = strpos($mainContent, "load_plugin_textdomain('notification-hub'") !== false;
$domainPathSet = strpos($mainContent, 'Domain Path: /languages') !== false;

echo 'load_plugin_textdomain notification-hub: ' . ($textdomainLoaded ? 'PASS' : 'FAIL') . PHP_EOL;
echo 'Domain Path /languages: ' . ($domainPathSet ? 'PASS' : 'FAIL') . PHP_EOL;

if (!$textdomainLoaded || !$domainPathSet) {
    $allPass = false;
}

$faPo = file_get_contents($base . '/languages/notification-hub-fa_IR.po') ?: '';
$itPo = file_get_contents($base . '/languages/notification-hub-it_IT.po') ?: '';

$requiredPairs = [
    ['msgid "Settings"', 'msgstr "تنظیمات"', $faPo, 'fa_IR Settings'],
    ['msgid "Send Test Notification"', 'msgstr "ارسال اعلان آزمایشی"', $faPo, 'fa_IR Send Test Notification'],
    ['msgid "Notifications"', 'msgstr "اعلان‌ها"', $faPo, 'fa_IR Notifications'],
    ['msgid "Settings"', 'msgstr "Impostazioni"', $itPo, 'it_IT Settings'],
    ['msgid "Send Test Notification"', 'msgstr "Invia notifica di prova"', $itPo, 'it_IT Send Test Notification'],
    ['msgid "Notifications"', 'msgstr "Notifiche"', $itPo, 'it_IT Notifications'],
];

foreach ($requiredPairs as [$msgid, $msgstr, $content, $label]) {
    $ok = strpos($content, $msgid) !== false && strpos($content, $msgstr) !== false;
    echo $label . ': ' . ($ok ? 'PASS' : 'FAIL') . PHP_EOL;
    if (!$ok) {
        $allPass = false;
    }
}

echo 'TRANSLATION_SMOKE: ' . ($allPass ? 'PASS' : 'FAIL') . PHP_EOL;
if (!$allPass) {
    exit(1);
}
