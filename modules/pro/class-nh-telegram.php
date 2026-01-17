<?php
/**
 * NH_Telegram
 *
 * Telegram channel adapter (Pro).
 *
 * Loaded only when Pro is active and Loader includes this class.
 * Requires valid nh_telegram_bot_token and nh_telegram_chat_id options.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Telegram {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var NH_Core_Registry|mixed
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry) {
        $this->r = $registry;
    }

    /**
     * Check if this adapter supports a channel.
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @return bool
     */
    public function supports(string $channel): bool {
        return $channel === 'telegram';
    }

    /**
     * Send a notification via Telegram Bot API.
     *
     * Payload keys:
     * - title (string) Optional.
     * - body (string) Optional.
     * - source (string) Optional.
     *
     * @since 1.6.2
     * @param array $payload Message data.
     * @return bool True if sent successfully, false otherwise.
     */
    public function send(array $payload): bool {
        $token  = (string) get_option('nh_telegram_bot_token', '');
        $chatId = (string) get_option('nh_telegram_chat_id', '');

        if ($token === '' || $chatId === '') {
            return false;
        }

        $title  = isset($payload['title']) ? (string) $payload['title'] : esc_html__('Notification', 'notification-hub');
        $body   = isset($payload['body']) ? (string) $payload['body'] : '';
        $source = isset($payload['source']) ? (string) $payload['source'] : 'system';

        // Safe HTML formatting for Telegram.
        $text = sprintf(
            "<b>%s</b>\n%s\n\n<i>%s</i>",
            esc_html($title),
            esc_html($body),
            sprintf(
                /* translators: 1: Source, 2: Datetime. */
                esc_html__('Source: %1$s | %2$s', 'notification-hub'),
                esc_html(ucfirst($source)),
                esc_html((string) current_time('mysql'))
            )
        );

        $url  = "https://api.telegram.org/bot{$token}/sendMessage";
        $args = [
            'body' => [
                'chat_id'    => (int) $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ],
            'timeout' => 15,
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('NH_Telegram: send triggered for chat_id=' . $chatId);
        }

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('NH_Telegram error: ' . $response->get_error_message());
            }
            return false;
        }

        return true;
    }
}