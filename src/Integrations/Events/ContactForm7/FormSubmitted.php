<?php

namespace NotificationHub\Integrations\Events\ContactForm7;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Conditionals\IsContactForm7Active;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a CF7 form is submitted.
 *
 * @since 1.7.2
 */
final class FormSubmitted implements Integration {
    public function register(Loader $loader): void {
        if (!(new IsContactForm7Active())->passes()) {
            return;
        }

        $loader->addAction('wpcf7_mail_sent', [$this, 'handle'], 10, 1);
    }

    public function handle($contact_form): void {
        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('contactform7')
            ->type('form_submitted')
            ->title(__('Contact form submitted', 'notification-hub'))
            ->message(__('A Contact Form 7 submission occurred.', 'notification-hub'))
            ->status(0)
            ->priority(1)
            ->tags(['forms'])
            ->build();

        $repo->insert($data);
    }
}


