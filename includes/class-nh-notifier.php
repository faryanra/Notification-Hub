<?php
// Prevent direct access to this file for security reasons
if ( ! defined( 'ABSPATH' ) ) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

/**
 * Class NH_Notifier
 * This class handles sending notifications to external services like Telegram, Email, and Slack.
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

        $body = json_decode( wp_remote_retrieve_body( $response ) );  // Parse response
        return ! empty( $body->ok );  // Return true if 'ok' in response
    }

    // NEW: Added for phase 2-3
    /**
     * Sends an email notification.
     * Why? To notify via email as an alternative channel.
     * @param string $message The message to send.
     * @return bool Success.
     */
    public static function send_email( $message ) {
        $email = get_option( 'nh_email_address' );  // Stored email from settings
        if ( empty( $email ) ) {
            error_log( 'NH: Email address missing.' );  // Log error if not set
            return false;
        }
        $subject = 'Notification Hub Alert';  // Simple subject
        $result = wp_mail( $email, $subject, $message );  // Use WordPress mail function
        if ( ! $result ) {
            error_log( 'NH: Email send failed.' );  // Log if mail fails
        }
        return $result;
    }

    // NEW: Added for phase 2-3
    /**
     * Sends a message to Slack using webhook.
     * Why? To notify teams via Slack as an alternative channel.
     * @param string $message The message to send.
     * @return bool Success.
     */
    public static function send_slack( $message ) {
        $webhook = get_option( 'nh_slack_webhook' );  // Stored webhook URL from settings
        if ( empty( $webhook ) ) {
            error_log( 'NH: Slack webhook missing.' );  // Log error if not set
            return false;
        }
        $args = [
            'body' => wp_json_encode( [ 'text' => $message ] ),  // JSON payload for Slack
            'headers' => [ 'Content-Type' => 'application/json' ],  // Required header
        ];
        $response = wp_remote_post( $webhook, $args );  // Send POST request
        if ( is_wp_error( $response ) ) {
            error_log( 'NH: Slack send error - ' . $response->get_error_message() );  // Log WP error
            return false;
        }
        $body = json_decode( wp_remote_retrieve_body( $response ) );  // Parse response
        if ( ! $body || ! $body->ok ) {
            error_log( 'NH: Slack API error - ' . ( $body->error ?? 'Unknown' ) );  // Log Slack-specific error
            return false;
        }
        return true;
    }
}