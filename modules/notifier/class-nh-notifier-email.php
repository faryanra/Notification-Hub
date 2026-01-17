<?php
/**
 * NH_Notifier_Email
 *
 * Email notification handler (Free).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Notifier_Email {

    /**
     * Send an email notification.
     *
     * Expected payload keys:
     * - to (string) Optional. Recipient override.
     * - override_email_to (string) Optional. Multisite network override (highest priority).
     * - subject (string) Optional.
     * - title (string) Optional. Alias for subject.
     * - message (string) Optional. Plain or HTML.
     * - body (string) Optional. Alias for message.
     * - headers (array|string) Optional. wp_mail headers.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return bool Success status.
     */
    public static function send(array $payload): bool {
        $to      = self::get_recipient($payload);
        $subject = self::get_subject($payload);
        $message = self::get_message($payload);
        $headers = self::get_headers($payload);

        self::debug_log(
            sprintf(
                /* translators: 1: email recipient, 2: subject */
                __('Sending email to %1$s (Subject: %2$s)', 'notification-hub'),
                $to,
                $subject
            )
        );

        try {
            return (bool) wp_mail($to, $subject, $message, $headers);
        } catch (Throwable $e) {
            self::debug_log(
                sprintf(
                    /* translators: %s: error message */
                    __('Email send failed: %s', 'notification-hub'),
                    $e->getMessage()
                )
            );
            return false;
        }
    }

    /**
     * Get email recipient.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return string
     */
    private static function get_recipient(array $payload): string {
        // Network policy override.
        if (!empty($payload['override_email_to']) && is_string($payload['override_email_to'])) {
            return sanitize_email($payload['override_email_to']);
        }

        // Direct payload override (if caller wants it).
        if (!empty($payload['to']) && is_string($payload['to'])) {
            return sanitize_email($payload['to']);
        }

        return (string) get_option('nh_email_to', get_option('admin_email'));
    }

    /**
     * Get email subject.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return string
     */
    private static function get_subject(array $payload): string {
        if (!empty($payload['title']) && is_string($payload['title'])) {
            return wp_strip_all_tags($payload['title']);
        }

        if (!empty($payload['subject']) && is_string($payload['subject'])) {
            return wp_strip_all_tags($payload['subject']);
        }

        return __('Notification Hub', 'notification-hub');
    }

    /**
     * Get email message (HTML allowed).
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return string
     */
    private static function get_message(array $payload): string {
        if (!empty($payload['body']) && is_string($payload['body'])) {
            return $payload['body'];
        }

        if (!empty($payload['message']) && is_string($payload['message'])) {
            return $payload['message'];
        }

        // Safe fallback (avoid dumping payload into an email).
        return __('(Empty message)', 'notification-hub');
    }

    /**
     * Get wp_mail headers.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return array|string
     */
    private static function get_headers(array $payload) {
        if (isset($payload['headers'])) {
            return $payload['headers'];
        }

        // Default HTML email.
        return ['Content-Type: text/html; charset=UTF-8'];
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
            error_log($message);
        }
    }
}
