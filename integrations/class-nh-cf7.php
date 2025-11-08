<?php
// Contact Form 7 Integration (Clean + Unified Sending)

if (!defined('ABSPATH')) exit;

class NH_Int_CF7 {
    protected $r;

    public function __construct($registry) {
        $this->r = $registry;

        // Hook registration happens here
        if (defined('WPCF7_VERSION')) {
            add_action('wpcf7_mail_sent',   [$this, 'on_sent'],   10, 1);
            add_action('wpcf7_mail_failed', [$this, 'on_failed'], 10, 1);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('✅ NH_Int_CF7: CF7 hooks registered successfully');
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('⚠️ NH_Int_CF7: CF7 plugin not active, hooks skipped');
            }
        }
    }

    /* --------------------------------------------------------------
     *  Successful submission
     * ------------------------------------------------------------ */
    public function on_sent($contact_form) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚀 NH_Int_CF7::on_sent fired for form ID ' . $contact_form->id());
        }

        $e = [
            'source'  => 'cf7',
            'type'    => 'form_sent',
            'title'   => sprintf(__('CF7: %s', 'notification-hub'), $contact_form->title()),
            'message' => __('Mail sent successfully', 'notification-hub'),
            'context' => ['cf7_form_id' => (int)$contact_form->id()]
        ];

        // Insert into database (single record)
        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification($e);
        }

        // Send to all channels (no_log = true → prevents duplicate entries)
        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $payload = [
                'title'   => $e['title'],
                'body'    => $e['message'],
                'source'  => $e['source'],
                'no_log'  => true
            ];

            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
        }
    }

    /* --------------------------------------------------------------
     *  Failed submission
     * ------------------------------------------------------------ */
    public function on_failed($contact_form) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('🚨 NH_Int_CF7::on_failed fired for form ID ' . $contact_form->id());
        }

        $e = [
            'source'  => 'cf7',
            'type'    => 'form_failed',
            'title'   => sprintf(__('CF7: %s', 'notification-hub'), $contact_form->title()),
            'message' => __('Mail failed to send', 'notification-hub'),
            'context' => ['cf7_form_id' => (int)$contact_form->id()]
        ];

        // Insert into database (single record)
        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification($e);
        }

        // Send failure alerts to all channels
        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $payload = [
                'title'   => $e['title'],
                'body'    => $e['message'],
                'source'  => $e['source'],
                'no_log'  => true
            ];

            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
        }
    }
}
