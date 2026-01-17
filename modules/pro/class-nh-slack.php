<?php
/**
 * NH_Slack
 *
 * Slack channel adapter (Pro).
 *
 * Loaded only when Pro is active and Loader includes this class.
 * Requires a valid nh_slack_webhook option.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Slack {

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
        return $channel === 'slack';
    }

    /**
     * Send a formatted message to Slack via Incoming Webhook.
     *
     * Payload keys:
     * - title (string) Optional.
     * - body (string) Optional.
     * - source (string) Optional.
     *
     * @since 1.6.2
     * @param array $payload Message data.
     * @return bool True if successfully sent, false otherwise.
     */
    public function send(array $payload): bool {
        $webhook = (string) get_option('nh_slack_webhook', '');
        if ($webhook === '') {
            return false;
        }

        $title  = isset($payload['title']) ? (string) $payload['title'] : esc_html__('Notification', 'notification-hub');
        $body   = isset($payload['body']) ? (string) $payload['body'] : '';
        $source = isset($payload['source']) ? (string) $payload['source'] : 'system';

        $source_line = sprintf(
            /* translators: 1: Source, 2: Datetime. */
            esc_html__('Source: %1$s | %2$s', 'notification-hub'),
            ucfirst($source),
            (string) current_time('mysql')
        );

        // Slack uses mrkdwn; keep content plain (no HTML).
        $text = sprintf("*%s*\n%s\n_%s_", $title, $body, $source_line);

        $data = [
            'text'       => $text,
            'username'   => 'Notification Hub',
            'icon_emoji' => ':bell:',
        ];

        $args = [
            'body'    => wp_json_encode($data),
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'timeout' => 10,
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('NH_Slack: send triggered');
        }

        $response = wp_remote_post($webhook, $args);

        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('NH_Slack error: ' . $response->get_error_message());
            }
            return false;
        }

        return true;
    }
}