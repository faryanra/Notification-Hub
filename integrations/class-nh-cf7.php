<?php
// NH v1.3.3 — Contact Form 7 integration (Free, fixed)

if (!defined('ABSPATH')) exit;

class NH_Int_CF7 {
    protected $r;

    public function __construct($registry) {
        $this->r = $registry;

        // ✅ hook registration must happen here, not in a separate method
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

    public function on_sent($contact_form) {
        error_log('🚀 CF7 mail sent hook fired');

        $e = [
            'source'=>'cf7','type'=>'form_sent',
            'title'=>sprintf(__('CF7: %s','notification-hub'), $contact_form->title()),
            'message'=>__('Mail sent successfully','notification-hub'),
            'context'=>['form_id'=>$contact_form->id()]
        ];

        $db = $this->r->get_svc('db');
        if ($db) $db->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $notifier->send([
                'channel' => 'email',
                'title'   => $e['title'],
                'body'    => $e['message'],
                'source'  => $e['source'],
                'multi'   => ['slack','telegram']
            ]);
        }
    }

    public function on_failed($contact_form) {
        error_log('🚀 CF7 mail sent hook fired');

        $e = [
            'source'=>'cf7','type'=>'form_failed',
            'title'=>sprintf(__('CF7: %s','notification-hub'), $contact_form->title()),
            'message'=>__('Mail sent successfully','notification-hub'),
            'context'=>['form_id'=>$contact_form->id()]
        ];

        $db = $this->r->get_svc('db');
        if ($db) $db->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $notifier->send([
                'channel' => 'email',
                'title'   => $e['title'],
                'body'    => $e['message'],
                'source'  => $e['source'],
                'multi'   => ['slack','telegram']
            ]);
        }
    }
   

}
