<?php
/**
 * Email Notification Handler (Free)
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Notifier_Email {

    /**
     * Send email notification
     */
    public static function send(array $payload): bool {
        $to      = self::get_recipient($payload);
        $subject = self::get_subject($payload);
        $message = self::get_message($payload);

        if (WP_DEBUG) {
            error_log("📧 Sending email to: {$to} | Subject: {$subject}");
        }

        try {
            return wp_mail($to, $subject, $message);
        } catch (Throwable $e) {
            error_log('❌ Email send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email recipient
     */
    private static function get_recipient(array $payload): string {
        // Network policy override
        if (isset($payload['override_email_to'])) {
            return sanitize_email($payload['override_email_to']);
        }

        // User setting or admin email
        return get_option('nh_email_to', get_option('admin_email'));
    }

    /**
     * Get email subject
     */
    private static function get_subject(array $payload): string {
        return $payload['title'] ?? $payload['subject'] ?? __('Notification Hub', 'notification-hub');
    }

    /**
     * Get email message
     */
    private static function get_message(array $payload): string {
        return $payload['body'] ?? $payload['message'] ?? print_r($payload, true);
    }
}
