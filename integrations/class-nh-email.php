<?php
// Email channel adapter (Free)

if (!defined('ABSPATH')) exit;

class NH_Email {
    protected $r;

    public function __construct($registry){ $this->r = $registry; }

    public function supports(string $channel): bool {
        return $channel === 'email';
    }

    public function send(array $payload): bool {
        $to      = $payload['to'] ?? get_option('nh_email_to', get_option('admin_email'));
        $subject = $payload['subject'] ?? '[NH] Notification';
        $body    = $payload['body'] ?? '';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Basic email via wp_mail
        return wp_mail($to, $subject, wp_kses_post($body), $headers);
    }
}
