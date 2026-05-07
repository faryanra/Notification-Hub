<?php
namespace NotificationHub\Integrations\Events\WordPress;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when wp_mail successfully sends an email.
 *
 * @since 1.0.0
 */
final class EmailSent implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('wp_mail_succeeded', [$this, 'handle'], 10, 1);
    }

    /**
     * @param array $mail_data
     */
    public function handle($mail_data): void {
        if (!is_array($mail_data)) {
            return;
        }

        $subject = isset($mail_data['subject']) ? (string) $mail_data['subject'] : '';

        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('email_sent')
            ->title(__('Email sent', 'notification-hub'))
            ->message($subject !== '' ? sprintf(__('Subject: %s', 'notification-hub'), $subject) : __('An email was sent via wp_mail.', 'notification-hub'))
            ->status(0)
            ->priority(1)
            ->tags(['email'])
            ->build();

        $data['no_dispatch'] = true;

        $repo->insert($data);
    }
}




