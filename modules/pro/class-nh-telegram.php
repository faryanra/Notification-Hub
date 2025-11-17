<?php
// Telegram Channel (Pro)
// Loaded only when NH_License::is_pro() returns true and the Loader includes this class.

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles Telegram message delivery through Bot API.
 * Requires valid nh_telegram_bot_token and nh_telegram_chat_id options.
 */
class NH_Telegram {

    protected $r;

    public function __construct( $registry ) {
        $this->r = $registry;
    }

    /**
     * Determine if this channel supports the given type.
     */
    public function supports( string $channel ): bool {
        return $channel === 'telegram';
    }

    /**
     * Send a notification via Telegram Bot API.
     *
     * @param array $payload Message data: title, body, source, etc.
     * @return bool True if sent successfully, false otherwise.
     */
    public function send( array $payload ): bool {
        $token  = get_option( 'nh_telegram_bot_token' );
        $chatId = get_option( 'nh_telegram_chat_id' );

        if ( empty( $token ) || empty( $chatId ) ) {
            return false;
        }

        $title  = $payload['title']  ?? __( 'Notification', 'notification-hub' );
        $body   = $payload['body']   ?? '';
        $source = ucfirst( $payload['source'] ?? 'system' );

        // Safe text formatting
        $text = sprintf(
            "<b>%s</b>\n%s\n\n<i>Source: %s | %s</i>",
            htmlspecialchars( $title ),
            htmlspecialchars( $body ),
            htmlspecialchars( $source ),
            current_time( 'mysql' )
        );

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $args = [
            'body' => [
                'chat_id'    => (int) $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ],
            'timeout' => 15,
        ];

        // Debug log
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '📬 NH_Channel_Telegram triggered: token=' . substr( $token, 0, 8 ) . ' chat_id=' . $chatId );
        }

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( '❌ Telegram Error: ' . $response->get_error_message() );
            return false;
        }

        return true;
    }
}
