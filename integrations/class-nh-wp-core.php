<?php
// NH v1.2.1 — WP Core integration (Free) + Custom Hooks loader

if (!defined('ABSPATH')) exit;

class NH_Int_WP_Core {
    protected $r;

    public function __construct($registry){ 
        $this->r = $registry; 
    }

    /**
     * Register core WP hooks + load custom hooks from DB
     */
    public function hooks() {
        // Core events
        add_action('wp_insert_comment',        [$this,'on_comment'],        10, 2);
        add_action('transition_post_status',   [$this,'on_post_status'],    10, 3);
        add_action('user_register',            [$this,'on_user_register'],  10, 1);

        // Custom hooks from DB (table: wp_nh_hooks)
        $this->register_custom_hooks();
    }

    /**
     * Load custom hooks saved in DB and attach them with add_action()
     */
    protected function register_custom_hooks() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';
        if ($wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s", $table
        )) !== $table) {
            return; // hooks table not found
        }

        $rows = $wpdb->get_results("SELECT id, action_name, title, channels FROM {$table}");
        if (empty($rows)) return;

        foreach ($rows as $row) {
            $hook_name = trim((string)($row->action_name ?? ''));
            if ($hook_name === '') continue;

            // Decode channels JSON → default to ['email']
            $channels = $this->decode_channels($row->channels ?? '');
            $primary  = $channels[0] ?? 'email';
            $multi    = array_values(array_slice($channels, 1));

            // Attach!
            add_action($hook_name, function(...$args) use ($row, $hook_name, $primary, $multi) {
                // Prefer a payload array as first arg if provided
                $payload = $this->normalize_payload_from_args($row, $hook_name, $args);

                // Insert into DB (like core handlers do)
                $event = [
                    'source'  => 'hook',
                    'type'    => $hook_name,
                    'title'   => $payload['title'] ?? ($row->title ?: ('Hook: ' . $hook_name)),
                    'message' => $payload['body']  ?? '',
                    'context' => ['hook' => $hook_name]
                ];
                $db = $this->r->get_svc('db');
                if ($db && method_exists($db, 'insert_notification')) {
                    $db->insert_notification($event);
                }

                // Send via Notifier
                $notifier = $this->r->get_svc('notifier');
                if (!$notifier) return;

                $notifier->send([
                    'channel' => $primary,
                    'title'   => $event['title'],
                    'body'    => $event['message'],
                    'source'  => $event['source'],
                    'multi'   => $multi,
                ]);
            }, 10, 10); // allow up to 10 args
        }
    }

    /**
     * Decode channels JSON safely
     */
    protected function decode_channels($json) : array {
        if (!is_string($json) || $json === '') return ['email'];
        $arr = json_decode($json, true);
        if (!is_array($arr) || empty($arr)) return ['email'];
        // sanitize channel names
        return array_values(array_filter(array_map(function($c){
            $c = strtolower(trim((string)$c));
            return in_array($c, ['email','telegram','slack'], true) ? $c : null;
        }, $arr)));
    }

    /**
     * Build a payload from args if the first arg is an array with keys,
     * else fall back to a generic, readable message.
     */
    protected function normalize_payload_from_args($row, $hook_name, array $args) : array {
        if (!empty($args) && is_array($args[0])) {
            // Use developer-provided payload
            $p = $args[0];
            return [
                'title' => isset($p['title']) ? sanitize_text_field($p['title']) : ($row->title ?: ('Hook: ' . $hook_name)),
                'body'  => isset($p['body'])  ? sanitize_textarea_field($p['body']) : '',
                'source'=> isset($p['source'])? sanitize_text_field($p['source']) : 'hook'
            ];
        }
        // Fallback: turn args into a brief text
        $summary = '';
        if (!empty($args)) {
            // Limit verbosity
            $slice = array_slice($args, 0, 3);
            $summary = wp_json_encode($slice);
        }
        return [
            'title' => $row->title ?: ('Hook: ' . $hook_name),
            'body'  => $summary,
            'source'=> 'hook'
        ];
    }

    /* =========================
       Core Handlers
    ========================= */

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
        if (!$notifier) return;

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
        if (!$notifier) return;

        $notifier->send([
            'channel' => 'email',
            'title'   => $e['title'],
            'body'    => $e['message'],
            'source'  => $e['source'],
            'multi'   => ['slack','telegram']
        ]);
    } 

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
        if (!$notifier) return;

        $notifier->send([
            'channel' => 'email',
            'title'   => $e['title'],
            'body'    => $e['message'],
            'source'  => $e['source'],
            'multi'   => ['slack','telegram']
        ]);
    }
}
