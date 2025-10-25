<?php
// Notifier router (Free + Pro)

if (!defined('ABSPATH')) exit;

interface NH_Notifier_Channel {
    public function supports(string $channel): bool;
    public function send(array $payload): bool;
}

class NH_Notifier {
    protected $r;
    protected $channels = [];

    public function __construct($registry) {
        $this->r = $registry;
        $this->channels = [
            new NH_Channel_Email($this->r),
            new NH_Channel_Telegram($this->r),
            new NH_Channel_Slack($this->r)
        ];

        // 🔹 Register async hook
        add_action('nh_async_send', [$this, 'send']);
    }

    /**
     * Send asynchronously using WP-Cron
     */
    public function send_async(array $payload) {
        if (!wp_next_scheduled('nh_async_send', [$payload])) {
            wp_schedule_single_event(time() + 2, 'nh_async_send', [$payload]);
        }
    }

    /**
     * Send to one or multiple channels
     */
    public function send(array $payload) {
        error_log('🚀 NH_Notifier::send() reached');
        $pro = class_exists('NH_License') ? NH_License::is_pro() : false;

        $target = $payload['channel'] ?? 'email';
        $results = [];

        if (!$pro && in_array($target, ['telegram', 'slack'])) {
            $target = 'email';
        }

        foreach ($this->channels as $ch) {
            if ($ch->supports($target)) {
                $results[$target] = $ch->send($payload);
            }
        }

        if ($pro && isset($payload['multi']) && is_array($payload['multi'])) {
            foreach ($payload['multi'] as $extra) {
                foreach ($this->channels as $ch) {
                    if ($ch->supports($extra)) {
                        $results[$extra] = $ch->send($payload);
                    }
                }
            }
        }

        return !empty(array_filter($results));
    }
}
