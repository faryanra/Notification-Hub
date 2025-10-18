<?php
// Prevent direct access to this file for security reasons
if ( ! defined( 'ABSPATH' ) ) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

/**
 * Class NH_Notifier
 * This class handles sending notifications to external services like Telegram.
 * It uses stored settings for API keys.
 */
class NH_Notifier {

    /**
     * Sends a message to Telegram using the stored bot token and chat ID.
     * Why? To notify users externally when a new notification is added.
     * @param string $message The message to send.
     * @return bool True on success, false on failure.
     */
    public static function send_telegram( $message ) {
        $token = get_option( 'nh_telegram_token' );  // Get stored bot token
        $chat_id = get_option( 'nh_telegram_chat_id' );  // Get stored chat ID
        if ( empty( $token ) || empty( $chat_id ) ) {
            error_log( 'NH: Telegram settings missing.' );  // Log error if settings not set
            return false;
        }

        $url = "https://api.telegram.org/bot$token/sendMessage";  // Telegram API endpoint
        $args = [
            'body' => [
                'chat_id' => $chat_id,
                'text'    => sanitize_text_field( $message ),  // Sanitize message for safety
            ],
        ];

        $response = wp_remote_post( $url, $args );  // Send POST request
        if ( is_wp_error( $response ) ) {
            error_log( 'NH: Telegram send error - ' . $response->get_error_message() );  // Log WP error
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ) );
        if ( empty( $body->ok ) ) {
            error_log( 'NH: Telegram API error - ' . ( $body->description ?? 'Unknown' ) );  // Log API description like 'Bad Request'
            return false;
        }
        return true;

    }
}