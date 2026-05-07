<?php
namespace NotificationHub\Integrations\Events\ContactForm7;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Conditionals\IsContactForm7Active;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a CF7 form is submitted.
 *
 * @since 1.0.0
 */
final class FormSubmitted implements Integration {
    public function register(Loader $loader): void {
        if (!(new IsContactForm7Active())->passes()) {
            return;
        }

        $loader->addAction('wpcf7_mail_sent', [$this, 'handle'], 10, 1);
    }

    public function handle($contact_form): void {
        if (!is_object($contact_form)) {
            return;
        }

        $form_id = 0;
        $form_title = '';

        if (method_exists($contact_form, 'id')) {
            $form_id = (int) $contact_form->id();
        } elseif (method_exists($contact_form, 'get_id')) {
            $form_id = (int) $contact_form->get_id();
        }

        if (method_exists($contact_form, 'title')) {
            $form_title = (string) $contact_form->title();
        } elseif (method_exists($contact_form, 'get_title')) {
            $form_title = (string) $contact_form->get_title();
        }

        if ($form_id <= 0 && $form_title === '') {
            return;
        }

        if ($form_title === '') {
            $form_title = sprintf(__('Form #%d', 'notification-hub'), $form_id);
        }

        $preview = $this->extractSubmissionPreview();
        $admin_link = admin_url('admin.php?page=wpcf7');
        $repo = new NotificationsRepository();

        $message = __('A Contact Form 7 submission was received.', 'notification-hub');
        if ($preview !== '') {
            $message = sprintf(
                __('A Contact Form 7 submission was received. Preview: %s', 'notification-hub'),
                $preview
            );
        }

        $data = NotificationBuilder::make()
            ->source('contactform7')
            ->type('form_submitted')
            ->title(sprintf(__('New form submission: %s', 'notification-hub'), wp_strip_all_tags($form_title)))
            ->message($message)
            ->status(0)
            ->priority(1)
            ->tags(['forms'])
            ->context([
                'form_id' => $form_id,
                'form_title' => wp_strip_all_tags($form_title),
                'submission_preview' => $preview,
                'admin_link' => $admin_link,
                'cta_label' => __('Open Forms', 'notification-hub'),
            ])
            ->link($admin_link)
            ->build();

        $repo->insert($data);
    }

    private function extractSubmissionPreview(): string {
        if (!class_exists('\\WPCF7_Submission')) {
            return '';
        }

        $submission = \WPCF7_Submission::get_instance();
        if (!$submission || !method_exists($submission, 'get_posted_data')) {
            return '';
        }

        $posted = $submission->get_posted_data();
        if (!is_array($posted)) {
            return '';
        }

        foreach ($posted as $key => $value) {
            $field = sanitize_key((string) $key);
            if ($field === '' || strpos($field, '_') === 0) {
                continue;
            }

            if (in_array($field, ['g-recaptcha-response', 'recaptcha', 'captcha'], true)) {
                continue;
            }

            $line = $this->normalizePostedValue($value);
            if ($line !== '') {
                return wp_trim_words($line, 18, '...');
            }
        }

        return '';
    }

    /**
     * @param mixed $value
     */
    private function normalizePostedValue($value): string {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (!is_scalar($item)) {
                    continue;
                }

                $txt = trim(wp_strip_all_tags((string) $item));
                if ($txt !== '') {
                    $parts[] = $txt;
                }
            }

            return implode(', ', $parts);
        }

        if (!is_scalar($value)) {
            return '';
        }

        return trim(wp_strip_all_tags((string) $value));
    }
}



