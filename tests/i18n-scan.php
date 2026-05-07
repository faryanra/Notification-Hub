<?php
/**
 * Heuristic scan for potentially non-translated user-facing strings.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/i18n-scan.php
 */

$base = dirname(__DIR__);
$dirs = [$base . '/src', $base . '/templates', $base . '/notification-hub.php'];
$files = [];

foreach ($dirs as $path) {
    if (is_dir($path)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($it as $fileInfo) {
            if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
                $files[] = $fileInfo->getPathname();
            }
        }
    } elseif (is_file($path)) {
        $files[] = $path;
    }
}

$skipTranslationFns = '/__\(|_e\(|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_x\(|_n\(|_nx\(|_ex\(/';
$looksLikeText = '/([\'"])[A-Za-z][^\'"]*\s[^\'"]*\1/';
$outputContext = '/echo|wp_die|submit_button|add_menu_page|add_submenu_page|wp_send_json_(error|success)|<h1|<h2|<p|label|placeholder|title|description|notice|message|error|success|button/';

$candidates = [];
foreach ($files as $file) {
    $rel = ltrim(str_replace('\\', '/', str_replace($base, '', $file)), '/');
    $lines = file($file);
    foreach ($lines as $idx => $line) {
        if (preg_match($looksLikeText, $line) !== 1) {
            continue;
        }
        if (preg_match($skipTranslationFns, $line) === 1) {
            continue;
        }
        if (preg_match($outputContext, $line) !== 1) {
            continue;
        }

        $candidates[] = sprintf('%s:%d:%s', $rel, $idx + 1, trim($line));
    }
}

echo 'I18N_SCAN_CANDIDATES=' . count($candidates) . PHP_EOL;
foreach ($candidates as $row) {
    echo $row . PHP_EOL;
}
