<?php
/**
 * Telegram Notification Handler (Pro)
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Notifier_Telegram {

    /**
     * Send Telegram notification
     */
    public static function send(array $payload): bool {
        if (!self::is_configured()) {
            error_log('⚠️ Telegram not configured (missing token or chat_id)');
            return false;
        }

        $token   = self::get_token();
        $chat_id = get_option('nh_telegram_chat_id', '');
        $text    = $payload['body'] ?? $payload['message'] ?? '(no message)';

        if (WP_DEBUG) {
            error_log("📨 Sending Telegram to chat: {$chat_id}");
        }

        try {
            $response = wp_remote_post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'body'    => [
                        'chat_id' => $chat_id,
                        'text'    => $text,
                    ],
                    'timeout' => 5,
                ]
            );

            if (is_wp_error($response)) {
                error_log('❌ Telegram error: ' . $response->get_error_message());
                return false;
            }

            return wp_remote_retrieve_response_code($response) === 200;

        } catch (Throwable $e) {
            error_log('❌ Telegram send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if Telegram is configured
     */
    private static function is_configured(): bool {
        return !empty(self::get_token()) && !empty(get_option('nh_telegram_chat_id', ''));
    }

    /**
     * Get Telegram bot token
     */
    private static function get_token(): string {
        return get_option('nh_telegram_bot_token', '') ?: get_option('nh_telegram_token', '');
    }
}
