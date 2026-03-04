<?php

namespace NotificationHub\Premium\Integrations\Channels\Email;

use NotificationHub\Presenters\TemplateLoader;
use NotificationHub\Repositories\SettingsRepository;

/**
 * Email sender (template-based).
 *
 * Uses SettingsRepository general.email_to by default; payload can override.
 *
 * @since 1.7.2
 */
final class EmailSender {
    private SettingsRepository $settings;

    public function __construct(?SettingsRepository $settings = null) {
        $this->settings = $settings ?: new SettingsRepository();
    }

    public function send(array $payload): bool {
        $general = $this->settings->getGeneral();

        $to = isset($payload['to']) ? (string) $payload['to'] : '';
        if ($to === '' && !empty($general['email_to'])) {
            $to = (string) $general['email_to'];
        }
        if ($to === '') {
            $to = (string) get_option('admin_email');
        }

        $subject = isset($payload['subject']) ? (string) $payload['subject'] : '';
        if ($subject === '') {
            $subject = isset($payload['title']) ? (string) $payload['title'] : __('Notification Hub', 'notification-hub');
        }

        $data = [
            'title'        => isset($payload['title']) ? (string) $payload['title'] : $subject,
            'summary'      => isset($payload['body']) ? (string) $payload['body'] : (isset($payload['message']) ? (string) $payload['message'] : ''),
            'link'         => isset($payload['link']) ? (string) $payload['link'] : '',
            'context'      => isset($payload['context']) && is_array($payload['context']) ? $payload['context'] : [],
            'site_name'    => (string) get_bloginfo('name'),
            'site_url'     => (string) home_url('/'),
            'cta_label'    => isset($payload['cta_label']) ? (string) $payload['cta_label'] : '',
            'source_human' => isset($payload['source']) ? (string) $payload['source'] : '',
            'type_human'   => isset($payload['type']) ? (string) $payload['type'] : '',
        ];

        $html = (new TemplateLoader())->render('notifications/email.php', $data);
        if ($html === '') {
            $html = (string) $data['summary'];
        }

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        return (bool) wp_mail($to, $subject, $html, $headers);
    }
}
