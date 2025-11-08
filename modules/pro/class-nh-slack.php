<?php
// Slack Channel (Pro)
// Loaded only when NH_License::is_pro() returns true and included by the Loader.

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles message delivery via Slack Incoming Webhook.
 * Requires a valid nh_slack_webhook option.
 */
class NH_Slack {

    protected $r;

    public function __construct( $registry ) {
        $this->r = $registry;
    }

    /**
     * Determine if this channel supports the given type.
     */
    public function supports( string $channel ): bool {
        return $channel === 'slack';
    }

    /**
     * Send a formatted message to Slack via Incoming Webhook.
     *
     * @param array $payload Message data: title, body, source, etc.
     * @return bool True if successfully sent, false otherwise.
     */
    public function send( array $payload ): bool {
        $webhook = get_option( 'nh_slack_webhook' );
        if ( empty( $webhook ) ) {
            return false;
        }

        $title  = $payload['title']  ?? __( 'Notification', 'notification-hub' );
        $body   = $payload['body']   ?? '';
        $source = ucfirst( $payload['source'] ?? 'system' );

        $data = [
            'text'       => "*{$title}*\n{$body}\n_Source: {$source} | " . current_time( 'mysql' ) . "_",
            'username'   => 'Notification Hub',
            'icon_emoji' => ':bell:',
        ];

        $args = [
            'body'    => wp_json_encode( $data ),
            'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'timeout' => 10,
        ];

        // Debug log
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '📬 NH_Channel_Slack triggered: webhook=' . substr( $webhook, 0, 40 ) . '...' );
        }

        $response = wp_remote_post( $webhook, $args );

        if ( is_wp_error( $response ) ) {
            error_log( '❌ Slack Error: ' . $response->get_error_message() );
            return false;
        }

        return true;
    }
}
