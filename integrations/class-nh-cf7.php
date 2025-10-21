<?php
// NH v1.2.0 — Contact Form 7 integration (Free)

if (!defined('ABSPATH')) exit;

class NH_Int_CF7 {
    protected $r;
    public function __construct($registry){ $this->r=$registry; }
    public function hooks() {
        if (!defined('WPCF7_VERSION')) return;
        add_action('wpcf7_mail_sent', [$this,'on_sent'], 10, 1);
        add_action('wpcf7_mail_failed', [$this,'on_failed'], 10, 1);
    }
    public function on_sent($contact_form) {
        $e = [
          'source'=>'cf7','type'=>'form_sent',
          'title'=>sprintf(__('CF7: %s','notification-hub'), $contact_form->title()),
          'message'=>__('Mail sent successfully','notification-hub'),
          'context'=>['form_id'=>$contact_form->id()]
        ];
        $this->r->get_svc('db')->insert_notification($e);
    }
    public function on_failed($contact_form) {
        $e = [
          'source'=>'cf7','type'=>'form_failed',
          'title'=>sprintf(__('CF7: %s','notification-hub'), $contact_form->title()),
          'message'=>__('Mail failed to send','notification-hub'),
          'context'=>['form_id'=>$contact_form->id()]
        ];
        $this->r->get_svc('db')->insert_notification($e);
    }
}
