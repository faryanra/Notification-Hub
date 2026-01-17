<?php
/**
 * NH_Int_CF7
 *
 * Contact Form 7 integration for Notification Hub.
 *
 * - Listens to successful and failed CF7 submissions.
 * - Stores a single notification record and fans out to all channels.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Int_CF7 {

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

        if (defined('WPCF7_VERSION')) {
            add_action('wpcf7_mail_sent', [$this, 'on_sent'], 10, 1);
            add_action('wpcf7_mail_failed', [$this, 'on_failed'], 10, 1);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('NH_Int_CF7: CF7 hooks registered successfully');
            }
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('NH_Int_CF7: CF7 plugin not active, hooks skipped');
        }
    }

    /**
     * Handle successful submission.
     *
     * @since 1.6.2
     * @param WPCF7_ContactForm $contact_form CF7 form.
     * @return void
     */
    public function on_sent($contact_form) {
        if (defined('WP_DEBUG') && WP_DEBUG && is_object($contact_form)) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('NH_Int_CF7::on_sent fired for form ID ' . $contact_form->id());
        }

        $form_title = is_object($contact_form) && method_exists($contact_form, 'title')
            ? (string) $contact_form->title()
            : '';

        /* translators: %s: Contact Form 7 form title. */
        $title = sprintf(esc_html__('CF7: %s', 'notification-hub'), $form_title);

        $e = [
            'source'  => 'cf7',
            'type'    => 'form_sent',
            'title'   => $title,
            'message' => esc_html__('Mail sent successfully.', 'notification-hub'),
            'context' => [
                'cf7_form_id' => is_object($contact_form) && method_exists($contact_form, 'id')
                    ? (int) $contact_form->id()
                    : 0,
            ],
        ];

        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification($e);
        }

        $notifier = $this->r->get_svc('notifier');
        if ($notifier && method_exists($notifier, 'queue_send')) {
            $payload = [
                'title'  => $e['title'],
                'body'   => $e['message'],
                'source' => $e['source'],
                'no_log' => true,
            ];

            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
        }
    }

    /**
     * Handle failed submission.
     *
     * @since 1.6.2
     * @param WPCF7_ContactForm $contact_form CF7 form.
     * @return void
     */
    public function on_failed($contact_form) {
        if (defined('WP_DEBUG') && WP_DEBUG && is_object($contact_form)) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('NH_Int_CF7::on_failed fired for form ID ' . $contact_form->id());
        }

        $form_title = is_object($contact_form) && method_exists($contact_form, 'title')
            ? (string) $contact_form->title()
            : '';

        /* translators: %s: Contact Form 7 form title. */
        $title = sprintf(esc_html__('CF7: %s', 'notification-hub'), $form_title);

        $e = [
            'source'  => 'cf7',
            'type'    => 'form_failed',
            'title'   => $title,
            'message' => esc_html__('Mail failed to send.', 'notification-hub'),
            'context' => [
                'cf7_form_id' => is_object($contact_form) && method_exists($contact_form, 'id')
                    ? (int) $contact_form->id()
                    : 0,
            ],
        ];

        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification($e);
        }

        $notifier = $this->r->get_svc('notifier');
        if ($notifier && method_exists($notifier, 'queue_send')) {
            $payload = [
                'title'  => $e['title'],
                'body'   => $e['message'],
                'source' => $e['source'],
                'no_log' => true,
            ];

            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
        }
    }
}