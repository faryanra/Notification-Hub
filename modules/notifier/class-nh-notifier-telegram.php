<?php
/**
 * NH_Notifier_Telegram
 *
 * Telegram notification handler (Pro).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier_Telegram {

    /**
     * Send Telegram notification.
     *
     * Payload keys:
     * - body|message (string) Optional.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return bool
     */
    public static function send(array $payload): bool {
        if (!self::is_configured()) {
            self::debug_log(esc_html__('Telegram is not configured (missing token or chat ID).', 'notification-hub'));
            return false;
        }

        $token  = self::get_token();
        $chatid = sanitize_text_field((string) get_option('nh_telegram_chat_id', ''));
        $text   = self::get_message_text($payload);

        try {
            $response = wp_remote_post(
                self::build_api_url($token),
                [
                    'body'    => [
                        'chat_id' => $chatid,
                        'text'    => $text,
                    ],
                    'timeout' => 8,
                ]
            );

            if (is_wp_error($response)) {
                self::debug_log(
                    sprintf(
                        /* translators: %s: WP_Error message */
                        __('Telegram request error: %s', 'notification-hub'),
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
                        __('Telegram send failed (HTTP %d).', 'notification-hub'),
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
                    __('Telegram send failed: %s', 'notification-hub'),
                    $e->getMessage()
                )
            );
            return false;
        }
    }

    /**
     * Check if Telegram is configured.
     *
     * @since 1.6.2
     * @return bool
     */
    private static function is_configured(): bool {
        return (self::get_token() !== '') && ((string) get_option('nh_telegram_chat_id', '') !== '');
    }

    /**
     * Get Telegram bot token.
     *
     * @since 1.6.2
     * @return string
     */
    private static function get_token(): string {
        $token = (string) get_option('nh_telegram_bot_token', '');
        if ($token !== '') {
            return $token;
        }

        // Back-compat.
        return (string) get_option('nh_telegram_token', '');
    }

    /**
     * Build Telegram API URL.
     *
     * @since 1.6.2
     * @param string $token Bot token.
     * @return string
     */
    private static function build_api_url(string $token): string {
        return sprintf('https://api.telegram.org/bot%s/sendMessage', rawurlencode($token));
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
     * Debug logger.
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