<?php
/**
 * Telegram payload and diagnostics smoke tests for Notification Hub.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/telegram-payload-test.php
 */

namespace NotificationHub\Repositories {
    final class SettingsRepository {
        public function getChannels(): array {
            return [
                'telegram_bot_token' => 'default-token',
                'telegram_chat_id' => 'default-chat',
                'slack_webhook' => '',
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
            return 'Telegram test message';
        }
    }
}

namespace {
    define('ABSPATH', __DIR__);
    define('WP_DEBUG', true);

    $GLOBALS['nh_filters'] = [];
    $GLOBALS['nh_remote_queue'] = [];

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

    function wp_parse_url(string $url, int $component = -1) {
        return parse_url($url, $component);
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

    function add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {
        $GLOBALS['nh_filters'][$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    function apply_filters(string $hook, $value, ...$args) {
        if (empty($GLOBALS['nh_filters'][$hook]) || !is_array($GLOBALS['nh_filters'][$hook])) {
            return $value;
        }

        usort(
            $GLOBALS['nh_filters'][$hook],
            static function (array $a, array $b): int {
                return ($a['priority'] <=> $b['priority']);
            }
        );

        $filtered = $value;
        foreach ($GLOBALS['nh_filters'][$hook] as $entry) {
            $accepted = max(1, (int) $entry['accepted_args']);
            $callArgs = array_slice(array_merge([$filtered], $args), 0, $accepted);
            $filtered = call_user_func_array($entry['callback'], $callArgs);
        }

        return $filtered;
    }

    function is_wp_error($thing): bool {
        return false;
    }

    function nh_push_remote_response(array $response): void {
        $GLOBALS['nh_remote_queue'][] = $response;
    }

    function wp_remote_post(string $url, array $args) {
        $GLOBALS['nh_telegram_last_post'] = [
            'url' => $url,
            'args' => $args,
        ];

        if (!empty($GLOBALS['nh_remote_queue'])) {
            return array_shift($GLOBALS['nh_remote_queue']);
        }

        return [
            'response' => ['code' => 200],
            'body' => '{"ok":true,"result":{"message_id":1}}',
        ];
    }

    function wp_remote_retrieve_response_code(array $response): int {
        return isset($response['response']['code']) ? (int) $response['response']['code'] : 0;
    }

    function wp_remote_retrieve_body(array $response): string {
        return isset($response['body']) ? (string) $response['body'] : '';
    }

    require_once __DIR__ . '/../src/Channels/TelegramSender.php';

    $sender = new \NotificationHub\Channels\TelegramSender();
    $results = [];

    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":true,"result":{"message_id":1}}',
    ]);
    $resPublic = $sender->sendWithResult([
        'token' => 'token-1',
        'chat_id' => '12345',
        'title' => 'Public URL',
        'summary' => 'Public URL payload.',
        'cta_label' => 'Open details',
        'link' => 'https://example.com/wp-admin/comment.php?action=editcomment&c=42',
    ]);
    $bodyPublic = $GLOBALS['nh_telegram_last_post']['args']['body'] ?? [];
    $results['PUBLIC_URL_HAS_REPLY_MARKUP'] = !empty($resPublic['ok']) && isset($bodyPublic['reply_markup']);

    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":true,"result":{"message_id":2}}',
    ]);
    $resLocalNoFilter = $sender->sendWithResult([
        'token' => 'token-2',
        'chat_id' => '12345',
        'title' => 'Local URL',
        'summary' => 'Local URL payload.',
        'cta_label' => 'Open details',
        'link' => 'http://localhost/MyProject/wp-admin/admin.php?page=nh-dashboard',
    ]);
    $bodyLocalNoFilter = $GLOBALS['nh_telegram_last_post']['args']['body'] ?? [];
    $results['LOCALHOST_URL_NO_FILTER_NO_REPLY_MARKUP'] = !empty($resLocalNoFilter['ok']) && !isset($bodyLocalNoFilter['reply_markup']);

    add_filter(
        'notification_hub_allow_local_telegram_buttons',
        static function ($allow, $url) {
            return true;
        },
        10,
        2
    );
    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":true,"result":{"message_id":3}}',
    ]);
    $resLocalWithFilter = $sender->sendWithResult([
        'token' => 'token-3',
        'chat_id' => '12345',
        'title' => 'Local URL with filter',
        'summary' => 'Local URL payload.',
        'cta_label' => 'Open details',
        'link' => 'http://localhost/MyProject/wp-admin/admin.php?page=nh-dashboard',
    ]);
    $bodyLocalWithFilter = $GLOBALS['nh_telegram_last_post']['args']['body'] ?? [];
    $results['LOCALHOST_URL_WITH_FILTER_HAS_REPLY_MARKUP'] = !empty($resLocalWithFilter['ok']) && isset($bodyLocalWithFilter['reply_markup']);

    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":true,"result":{"message_id":4}}',
    ]);
    $resComment = $sender->sendWithResult([
        'token' => 'token-4',
        'chat_id' => '12345',
        'source' => 'wordpress',
        'type' => 'comment_posted',
        'title' => 'New Comment',
        'summary' => 'Comment payload.',
        'cta_label' => 'Open Comment',
        'link' => 'https://example.com/wp-admin/comment.php?action=editcomment&c=42',
    ]);
    $commentBody = $GLOBALS['nh_telegram_last_post']['args']['body'] ?? [];
    $commentMarkup = isset($commentBody['reply_markup']) ? json_decode((string) $commentBody['reply_markup'], true) : [];
    $commentText = $commentMarkup['inline_keyboard'][0][0]['text'] ?? '';
    $results['COMMENT_USES_CTA_LABEL_OPEN_COMMENT'] = !empty($resComment['ok']) && $commentText === 'Open Comment';

    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":true,"result":{"message_id":5}}',
    ]);
    $resOrder = $sender->sendWithResult([
        'token' => 'token-5',
        'chat_id' => '12345',
        'source' => 'woocommerce',
        'type' => 'order_created',
        'title' => 'New Order',
        'summary' => 'Order payload.',
        'cta_label' => 'Open Order',
        'link' => 'https://example.com/wp-admin/post.php?post=10&action=edit',
    ]);
    $orderBody = $GLOBALS['nh_telegram_last_post']['args']['body'] ?? [];
    $orderMarkup = isset($orderBody['reply_markup']) ? json_decode((string) $orderBody['reply_markup'], true) : [];
    $orderText = $orderMarkup['inline_keyboard'][0][0]['text'] ?? '';
    $results['WOO_ORDER_USES_CTA_LABEL_OPEN_ORDER'] = !empty($resOrder['ok']) && $orderText === 'Open Order';

    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":false,"description":"Bad Request: chat not found"}',
    ]);
    $resApiFail = $sender->sendWithResult([
        'token' => 'token-6',
        'chat_id' => '12345',
        'title' => 'API fail',
        'summary' => 'Mock API failure',
        'link' => 'https://example.com/a',
    ]);
    $results['TELEGRAM_OK_FALSE_RETURNS_DESCRIPTION'] = empty($resApiFail['ok']) && ($resApiFail['error'] ?? '') === 'Bad Request: chat not found';

    nh_push_remote_response([
        'response' => ['code' => 200],
        'body' => '{"ok":true,"result":{"message_id":99}}',
    ]);
    $resApiOk = $sender->sendWithResult([
        'token' => 'token-7',
        'chat_id' => '12345',
        'title' => 'API ok',
        'summary' => 'Mock API success',
        'link' => 'https://example.com/b',
    ]);
    $results['TELEGRAM_OK_TRUE_RETURNS_SUCCESS'] = !empty($resApiOk['ok']);

    $allPass = true;
    foreach ($results as $name => $ok) {
        echo $name . ': ' . ($ok ? 'PASS' : 'FAIL') . PHP_EOL;
        if (!$ok) {
            $allPass = false;
        }
    }

    if (!$allPass) {
        exit(1);
    }
}
