<?php
/**
 * Extract msgids for notification-hub text domain from PHP files.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/extract-msgids.php
 */

$base = dirname(__DIR__);
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
);

$pattern = '/\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1\s*,\s*[\'"]notification-hub[\'"]\s*\)/s';
$msgids = [];

foreach ($it as $fileInfo) {
    if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
        continue;
    }

    $path = $fileInfo->getPathname();
    if (strpos($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER) < 1) {
        continue;
    }

    foreach ($matches as $m) {
        $raw = stripcslashes($m[2]);
        $msgid = trim(str_replace(["\r\n", "\r"], "\n", $raw));
        if ($msgid === '') {
            continue;
        }
        $msgids[$msgid] = true;
    }
}

$keys = array_keys($msgids);
sort($keys, SORT_NATURAL | SORT_FLAG_CASE);
echo 'MSGIDS=' . count($keys) . PHP_EOL;
foreach ($keys as $msgid) {
    echo $msgid . PHP_EOL;
}
