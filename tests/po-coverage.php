<?php
/**
 * Quick PO translation coverage report.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/po-coverage.php
 */

$paths = [
    'fa_IR' => dirname(__DIR__) . '/languages/notification-hub-fa_IR.po',
    'it_IT' => dirname(__DIR__) . '/languages/notification-hub-it_IT.po',
];

foreach ($paths as $locale => $path) {
    $content = file_get_contents($path);
    if ($content === false) {
        echo $locale . ': FAIL (cannot read file)' . PHP_EOL;
        continue;
    }

    $lines = preg_split('/\R/', $content);
    if (!is_array($lines)) {
        echo $locale . ': FAIL (cannot parse lines)' . PHP_EOL;
        continue;
    }

    $entries = [];
    $msgid = null;
    $msgstr = null;
    $state = '';

    $flush = static function () use (&$entries, &$msgid, &$msgstr): void {
        if ($msgid !== null) {
            $entries[] = ['id' => $msgid, 'str' => $msgstr ?? ''];
        }
        $msgid = null;
        $msgstr = null;
    };

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'msgid ')) {
            $flush();
            $msgid = stripcslashes(trim(substr($line, 6), '"'));
            $msgstr = '';
            $state = 'id';
            continue;
        }

        if (str_starts_with($line, 'msgstr ')) {
            $msgstr = stripcslashes(trim(substr($line, 7), '"'));
            $state = 'str';
            continue;
        }

        if (str_starts_with($line, '"')) {
            $part = stripcslashes(trim($line, '"'));
            if ($state === 'id' && $msgid !== null) {
                $msgid .= $part;
            } elseif ($state === 'str' && $msgstr !== null) {
                $msgstr .= $part;
            }
        }
    }
    $flush();

    $total = 0;
    $translated = 0;
    $empty = [];
    foreach ($entries as $entry) {
        $id = (string) $entry['id'];
        $str = (string) $entry['str'];
        if ($id === '') {
            continue;
        }
        $total++;
        if ($str !== '') {
            $translated++;
        } else {
            $empty[] = $id;
        }
    }

    echo sprintf(
        '%s: translated=%d total=%d coverage=%.2f%% empty=%d',
        $locale,
        $translated,
        $total,
        $total > 0 ? ($translated / $total) * 100 : 0,
        count($empty)
    ) . PHP_EOL;

    if (!empty($empty)) {
        echo $locale . ' sample missing: ' . implode(' | ', array_slice($empty, 0, 20)) . PHP_EOL;
    }
}
