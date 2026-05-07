<?php
/**
 * Channel sender smoke checks (Email, Telegram, Slack) with WP stubs.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/channel-senders-smoke.php
 */

namespace NotificationHub\Repositories {
    final class SettingsRepository {
        public function getChannels(): array {
            return [
                'telegram_bot_token' => 'token-default',
                'telegram_chat_id' => 'chat-default',
                'slack_webhook' => 'https://hooks.slack.test/services/demo',
            ];
        }

        public function getGeneral(): array {
            return [
                'email_to' => 'admin@example.test',
            ];
        }
    }
}

namespace NotificationHub\Services {
    final class NotificationFormatter {
        public function format(array $payload): array {
            return $payload;
        }
    }
}

namespace NotificationHub\Presenters {
    final class TemplateLoader {
        public function render(string $template, array $data): string {
            return 'Smoke notification message';
        }
    }
}

namespace {
    define('ABSPATH', __DIR__);

    $GLOBALS['nh_remote_posts'] = [];
    $GLOBALS['nh_mail_calls'] = [];

    function __(string $text, string $domain = ''): string {
        return $text;
    }
    function esc_html__(string $text, string $domain = ''): string {
        return $text;
    }
    function esc_html(string $text): string {
        return $text;
    }
    function esc_url_raw(string $url): string {
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }
    function esc_url(string $url): string {
        return $url;
    }
    function wp_strip_all_tags(string $text): string {
        return strip_tags($text);
    }
    function wp_json_encode($value): string {
        return json_encode($value);
    }
    function sanitize_text_field(string $text): string {
        return trim($text);
    }
    function is_wp_error($thing): bool {
        return false;
    }
    function wp_remote_post(string $url, array $args) {
        $GLOBALS['nh_remote_posts'][] = ['url' => $url, 'args' => $args];
        return ['response' => ['code' => 200]];
    }
    function wp_remote_retrieve_response_code(array $response): int {
        return (int) ($response['response']['code'] ?? 0);
    }
    function get_option(string $name, $default = null) {
        return $name === 'admin_email' ? 'admin@example.test' : $default;
    }
    function get_bloginfo(string $show): string {
        return 'Demo Site';
    }
    function home_url(string $path = ''): string {
        return 'https://example.test' . $path;
    }
    function is_email(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    function wp_mail(string $to, string $subject, string $message, array $headers = []): bool {
        $GLOBALS['nh_mail_calls'][] = compact('to', 'subject', 'message', 'headers');
        return true;
    }

    require_once __DIR__ . '/../src/Channels/TelegramSender.php';
    require_once __DIR__ . '/../src/Channels/SlackSender.php';
    require_once __DIR__ . '/../src/Channels/EmailSender.php';

    $telegram = new \NotificationHub\Channels\TelegramSender();
    $slack = new \NotificationHub\Channels\SlackSender();
    $email = new \NotificationHub\Channels\EmailSender();

    $tg = $telegram->sendWithResult([
        'token' => 'token-x',
        'chat_id' => '10001',
        'title' => 'Comment notification',
        'summary' => 'New comment added.',
        'cta_label' => 'Open Comment',
        'link' => 'https://example.test/wp-admin/comment.php?action=editcomment&c=33',
    ]);

    $sl = $slack->sendWithResult([
        'webhook_url' => 'https://hooks.slack.test/services/demo',
        'title' => 'Slack test',
        'summary' => 'Slack sender smoke test',
    ]);

    $em = $email->sendWithResult([
        'to' => 'editor@example.test',
        'subject' => 'Email sender smoke',
        'title' => 'Email test',
        'summary' => 'Email sender smoke test',
    ]);

    $telegramOk = !empty($tg['ok']);
    $slackOk = !empty($sl['ok']);
    $emailOk = !empty($em['ok']) && !empty($GLOBALS['nh_mail_calls']);

    echo 'CHANNEL_TELEGRAM_SEND: ' . ($telegramOk ? 'PASS' : 'FAIL') . PHP_EOL;
    echo 'CHANNEL_SLACK_SEND: ' . ($slackOk ? 'PASS' : 'FAIL') . PHP_EOL;
    echo 'CHANNEL_EMAIL_SEND: ' . ($emailOk ? 'PASS' : 'FAIL') . PHP_EOL;

    if (!$telegramOk || !$slackOk || !$emailOk) {
        exit(1);
    }
}
