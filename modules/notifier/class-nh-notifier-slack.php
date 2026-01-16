<?php
/**
 * Slack Notification Handler (Pro)
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Notifier_Slack {

    /**
     * Send Slack notification
     */
    public static function send(array $payload): bool {
        $webhook = self::get_webhook();

        if (empty($webhook)) {
            error_log('⚠️ Slack webhook not configured');
            return false;
        }

        $text = $payload['body'] ?? $payload['message'] ?? '(no message)';

        if (WP_DEBUG) {
            error_log("💬 Sending Slack message");
        }

        try {
            $response = wp_remote_post($webhook, [
                'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
                'body'    => wp_json_encode(['text' => $text]),
                'timeout' => 5,
            ]);

            if (is_wp_error($response)) {
                error_log('❌ Slack error: ' . $response->get_error_message());
                return false;
            }

            return wp_remote_retrieve_response_code($response) === 200;

        } catch (Throwable $e) {
            error_log('❌ Slack send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Slack webhook URL
     */
    private static function get_webhook(): string {
        return get_option('nh_slack_webhook', '') ?: get_option('nh_slack_url', '');
    }
}
