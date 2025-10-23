<?php
// NH v1.2.0 — Slack Integration (Pro Feature)
// Sends messages using Slack Incoming Webhook URL.
// Setting: nh_slack_webhook

if (!defined('ABSPATH')) exit;

class NH_Channel_Slack implements NH_Notifier_Channel {
    protected $r;

    public function __construct($registry) { $this->r = $registry; }

    public function supports(string $channel): bool {
        return $channel === 'slack';
    }

    public function send(array $payload): bool {
        $webhook = get_option('nh_slack_webhook');
        if (empty($webhook)) return false;

        $title = $payload['title'] ?? __('Notification','notification-hub');
        $body  = $payload['body'] ?? '';
        $source = ucfirst($payload['source'] ?? 'system');

        $data = [
            'text' => "*{$title}*\n{$body}\n_Source: {$source} | ".current_time('mysql')."_",
            'username' => 'Notification Hub',
            'icon_emoji' => ':bell:'
        ];

        $response = wp_remote_post($webhook, [
            'body'    => wp_json_encode($data),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 10
        ]);

        return !is_wp_error($response);
    }
}
