<?php
/**
 * Live channel diagnostics in local WP runtime.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/live-channel-diagnostics.php
 */

$wpLoad = 'E:/XAMPP/htdocs/MyProject/wp-load.php';
if (!file_exists($wpLoad)) {
    echo "LIVE_DIAG: FAIL (wp-load.php not found)\n";
    exit(1);
}

require_once $wpLoad;

if (!class_exists('NotificationHub\\Services\\ServiceFactory')) {
    $localPlugin = dirname(__DIR__) . '/notification-hub.php';
    if (file_exists($localPlugin)) {
        require_once $localPlugin;
    }
}

if (!class_exists('NotificationHub\\Services\\ServiceFactory')) {
    echo "LIVE_DIAG: FAIL (ServiceFactory class missing)\n";
    exit(1);
}

$settings = new \NotificationHub\Repositories\SettingsRepository();
$general = $settings->getGeneral();
$channels = $settings->getChannels();

$mask = static function (string $value): string {
    $len = strlen($value);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }
    return substr($value, 0, 2) . str_repeat('*', max(0, $len - 4)) . substr($value, -2);
};

$dispatcher = \NotificationHub\Services\ServiceFactory::makeNotificationDispatcher();
$payload = [
    'title' => __('Notification Hub channel test', 'notification-hub'),
    'body' => __('This is a test notification from your website. If you can read this, the selected channel is working correctly.', 'notification-hub'),
    'source' => 'test',
    'type' => 'channel_test',
    'link' => admin_url('admin.php?page=nh-dashboard'),
    'cta_label' => __('Open Notification Hub', 'notification-hub'),
];

$emailResult = $dispatcher->sendNowDetailed('email', $payload);
$telegramResult = $dispatcher->sendNowDetailed('telegram', $payload);
$slackResult = $dispatcher->sendNowDetailed('slack', $payload);

echo 'GENERAL_EMAIL_TO=' . (string) ($general['email_to'] ?? '') . PHP_EOL;
echo 'WP_ADMIN_EMAIL=' . (string) get_option('admin_email') . PHP_EOL;
echo 'TG_TOKEN_SET=' . (!empty($channels['telegram_bot_token']) ? 'yes' : 'no') . PHP_EOL;
echo 'TG_TOKEN_MASK=' . $mask((string) ($channels['telegram_bot_token'] ?? '')) . PHP_EOL;
echo 'TG_CHAT_SET=' . (!empty($channels['telegram_chat_id']) ? 'yes' : 'no') . PHP_EOL;
echo 'TG_CHAT_MASK=' . $mask((string) ($channels['telegram_chat_id'] ?? '')) . PHP_EOL;
echo 'SLACK_SET=' . (!empty($channels['slack_webhook']) ? 'yes' : 'no') . PHP_EOL;

echo 'EMAIL_RESULT=' . wp_json_encode($emailResult) . PHP_EOL;
echo 'TELEGRAM_RESULT=' . wp_json_encode($telegramResult) . PHP_EOL;
echo 'SLACK_RESULT=' . wp_json_encode($slackResult) . PHP_EOL;
