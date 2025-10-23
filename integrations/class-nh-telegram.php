<?php
// NH v1.2.0 — Telegram Integration (Pro Feature)
// Handles sending notifications via Telegram Bot API.
// Settings: nh_telegram_bot_token, nh_telegram_chat_id

if (!defined('ABSPATH')) exit;

class NH_Channel_Telegram implements NH_Notifier_Channel {
    protected $r;

    public function __construct($registry) { 
        $this->r = $registry; 
    }

    public function supports(string $channel): bool {
        return $channel === 'telegram';
    }

    public function send(array $payload): bool {
        $token  = get_option('nh_telegram_bot_token');
        $chatId = get_option('nh_telegram_chat_id');
        if (empty($token) || empty($chatId)) return false;

        $title  = $payload['title']  ?? __('Notification', 'notification-hub');
        $body   = $payload['body']   ?? '';
        $source = ucfirst($payload['source'] ?? 'system');

        // NH v1.2.0 — safer text (no Markdown issues)
        $text = sprintf(
            "<b>%s</b>\n%s\n\n<i>Source: %s | %s</i>",
            htmlspecialchars($title),
            htmlspecialchars($body),
            htmlspecialchars($source),
            current_time('mysql')
        );

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $args = [
            'body' => [
                'chat_id' => (int)$chatId,
                'text' => $text,
                'parse_mode' => 'HTML' // ← switched from Markdown to HTML
            ],
            'timeout' => 15
        ];

        // Debug log
        error_log('📬 NH_Channel_Telegram triggered: token=' . substr($token, 0, 8) . ' chat_id=' . $chatId);

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            error_log('❌ Telegram Error: ' . $response->get_error_message());
        } 
        
        return !is_wp_error($response);
    }
}
