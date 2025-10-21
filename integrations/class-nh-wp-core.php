<?php
// NH v1.2.0 — WP Core integration (Free)

if (!defined('ABSPATH')) exit;

class NH_Int_WP_Core {
    protected $r;

    public function __construct($registry){ 
        $this->r = $registry; 
    }

    public function hooks() {
        add_action('wp_insert_comment', [$this,'on_comment'], 10, 2);
        add_action('transition_post_status', [$this,'on_post_status'], 10, 3);
        add_action('user_register', [$this,'on_user_register'], 10, 1);
    }

    public function on_comment($id, $comment) {
        $e = [
            'source'  => 'wp_core',
            'type'    => 'comment_new',
            'title'   => sprintf(__('New comment by %s','notification-hub'), $comment->comment_author),
            'message' => wp_kses_post(wp_trim_words($comment->comment_content, 20)),
            'context' => ['comment_id' => $id, 'post_id' => $comment->comment_post_ID]
        ];

        $this->r->get_svc('db')->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if (!$notifier) {
            error_log('❌ NH: Notifier not initialized yet (on_comment)');
            return;
        }

        $notifier->send([
            'channel' => 'email',
            'title'   => $e['title'],
            'body'    => $e['message'],
            'source'  => $e['source'],
            'multi'   => ['slack','telegram']
        ]);
    }

    public function on_post_status($new, $old, $post) {
        if ($new === $old) return;

        $e = [
            'source'  => 'wp_core',
            'type'    => 'post_status_changed',
            'title'   => sprintf(__('Post %d status: %s → %s','notification-hub'), $post->ID, $old, $new),
            'message' => esc_html(get_the_title($post->ID)),
            'context' => ['post_id'=>$post->ID,'old'=>$old,'new'=>$new]
        ];

        $this->r->get_svc('db')->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if (!$notifier) {
            error_log('❌ NH: Notifier not initialized yet (on_post_status)');
            return;
        }

        $notifier->send([
            'channel' => 'email',
            'title'   => $e['title'],
            'body'    => $e['message'],
            'source'  => $e['source'],
            'multi'   => ['slack','telegram']
        ]);
    } // ← این خط اضافه شد

    public function on_user_register($user_id) {
        $u = get_userdata($user_id);
        $e = [
            'source'  => 'wp_core',
            'type'    => 'user_registered',
            'title'   => sprintf(__('New user: %s','notification-hub'), $u->user_login),
            'message' => esc_html($u->user_email),
            'context' => ['user_id'=>$user_id]
        ];

        $this->r->get_svc('db')->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if (!$notifier) {
            error_log('❌ NH: Notifier not initialized yet (on_user_register)');
            return;
        }

        $notifier->send([
            'channel' => 'email',
            'title'   => $e['title'],
            'body'    => $e['message'],
            'source'  => $e['source'],
            'multi'   => ['slack','telegram']
        ]);
    }
}
