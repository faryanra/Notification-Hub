<?php
/**
 * NH_Email
 *
 * Email channel adapter (Free).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Email {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var NH_Core_Registry|mixed
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry) {
        $this->r = $registry;
    }

    /**
     * Check if this adapter supports a channel.
     *
     * @since 1.6.2
     * @param string $channel Channel slug.
     * @return bool
     */
    public function supports($channel) {
        return $channel === 'email';
    }

    /**
     * Send an email notification.
     *
     * Expected payload keys:
     * - to (string) Optional. Recipient email.
     * - subject (string) Optional. Email subject.
     * - body (string) Optional. Email HTML body.
     *
     * @since 1.6.2
     * @param array $payload Notification payload.
     * @return bool Success status.
     */
    public function send($payload) {
        $payload = is_array($payload) ? $payload : [];

        $to = isset($payload['to']) && is_string($payload['to']) && $payload['to'] !== ''
            ? $payload['to']
            : get_option('nh_email_to', get_option('admin_email'));

        $subject = isset($payload['subject']) && is_string($payload['subject']) && $payload['subject'] !== ''
            ? $payload['subject']
            : __('[NH] Notification', 'notification-hub');

        $body = isset($payload['body']) && is_string($payload['body'])
            ? $payload['body']
            : '';

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        return wp_mail($to, $subject, wp_kses_post($body), $headers);
    }
}
