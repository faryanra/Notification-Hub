<?php
$content = file_get_contents(dirname(__DIR__) . '/languages/notification-hub-fa_IR.po');
if ($content === false) {
    echo "READ_FAIL\n";
    exit(1);
}

echo 'HAS_PERSIAN=' . (strpos($content, 'تنظیمات') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'HAS_MOJIBAKE=' . (strpos($content, 'ØªÙ†Ø¸') !== false ? 'YES' : 'NO') . PHP_EOL;
