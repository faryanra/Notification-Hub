<?php
/**
 * NH_Notifier_Slack
 *
 * Slack notification handler (Pro).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier_Slack {

    /**
     * Send Slack notification.
     *
     * Payload keys:
     * - body|message (string) Optional.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return bool
     */
    public static function send(array $payload): bool {
        $webhook = self::get_webhook();

        if ($webhook === '') {
            self::debug_log(esc_html__('Slack webhook is not configured.', 'notification-hub'));
            return false;
        }

        $text = self::get_message_text($payload);

        try {
            $response = wp_remote_post(
                $webhook,
                [
                    'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
                    'body'    => wp_json_encode(['text' => $text]),
                    'timeout' => 8,
                ]
            );

            if (is_wp_error($response)) {
                self::debug_log(
                    sprintf(
                        /* translators: %s: WP_Error message */
                        __('Slack request error: %s', 'notification-hub'),
                        $response->get_error_message()
                    )
                );
                return false;
            }

            $code = (int) wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                self::debug_log(
                    sprintf(
                        /* translators: %d: HTTP status code */
                        __('Slack send failed (HTTP %d).', 'notification-hub'),
                        $code
                    )
                );
                return false;
            }

            return true;
        } catch (Throwable $e) {
            self::debug_log(
                sprintf(
                    /* translators: %s: exception message */
                    __('Slack send failed: %s', 'notification-hub'),
                    $e->getMessage()
                )
            );
            return false;
        }
    }

    /**
     * Get Slack webhook URL (with backward compatible option key).
     *
     * @since 1.6.2
     * @return string
     */
    private static function get_webhook(): string {
        $webhook = (string) get_option('nh_slack_webhook', '');
        if ($webhook !== '') {
            return esc_url_raw($webhook);
        }

        // Back-compat.
        return esc_url_raw((string) get_option('nh_slack_url', ''));
    }

    /**
     * Get message text from payload.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return string
     */
    private static function get_message_text(array $payload): string {
        if (!empty($payload['body']) && is_string($payload['body'])) {
            return $payload['body'];
        }

        if (!empty($payload['message']) && is_string($payload['message'])) {
            return $payload['message'];
        }

        return esc_html__('Empty message.', 'notification-hub');
    }

    /**
     * Debug logger (WP_DEBUG only).
     *
     * @since 1.6.2
     * @param string $message Log message.
     * @return void
     */
    private static function debug_log(string $message): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log($message);
        }
    }
}