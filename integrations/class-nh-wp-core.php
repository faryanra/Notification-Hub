<?php
// WP Core integration (Free) + Custom Hooks loader
// Handles built-in WP events (comment, post status, user register)
// and loads custom hooks from DB.

if (!defined('ABSPATH')) exit;

class NH_Int_WP_Core {
    protected $r;

    public function __construct($registry){ 
        $this->r = $registry; 
        $this->hooks();
    }

    public function hooks() {
        // Core events
        add_action('wp_insert_comment',       [$this, 'on_comment'],       10, 2);
        add_action('transition_post_status',  [$this, 'on_post_status'],   10, 3);
        add_action('user_register',           [$this, 'on_user_register'], 10, 1);

        // Custom hooks from DB (table: wp_nh_hooks)
        $this->register_custom_hooks();
    }

    protected function register_custom_hooks() {
        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) return;

        $rows = $wpdb->get_results("SELECT id, action_name, title, channels FROM {$table}");
        if (empty($rows)) return;

        foreach ($rows as $row) {
            $hook_name = trim((string)($row->action_name ?? ''));
            if ($hook_name === '') continue;

            $channels = $this->decode_channels($row->channels ?? '');
            $primary  = $channels[0] ?? 'email';

            add_action($hook_name, function (...$args) use ($row, $hook_name, $primary) {
                $payload = $this->normalize_payload_from_args($row, $hook_name, $args);

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

                $notifier = $this->r->get_svc('notifier');
                if ($notifier) {
                    $notifier->queue_send($primary, [
                        'title'   => $event['title'],
                        'body'    => $event['message'],
                        'source'  => $event['source'],
                        'no_log'  => true
                    ]);
                }
            }, 10, 10);
        }
    }

    protected function decode_channels($json): array {
        if (!is_string($json) || $json === '') return ['email'];
        $arr = json_decode($json, true);
        if (!is_array($arr) || empty($arr)) return ['email'];

        return array_values(array_filter(array_map(function ($c) {
            $c = strtolower(trim((string)$c));
            return in_array($c, ['email', 'telegram', 'slack'], true) ? $c : null;
        }, $arr)));
    }

    protected function normalize_payload_from_args($row, $hook_name, array $args): array {
        if (!empty($args) && is_array($args[0])) {
            $p = $args[0];
            return [
                'title'  => isset($p['title']) ? sanitize_text_field($p['title']) : ($row->title ?: ('Hook: ' . $hook_name)),
                'body'   => isset($p['body'])  ? sanitize_textarea_field($p['body']) : '',
                'source' => isset($p['source'])? sanitize_text_field($p['source']) : 'hook'
            ];
        }

        $summary = '';
        if (!empty($args)) {
            $slice = array_slice($args, 0, 3);
            $summary = wp_json_encode($slice);
        }

        return [
            'title'  => $row->title ?: ('Hook: ' . $hook_name),
            'body'   => $summary,
            'source' => 'hook'
        ];
    }

    /* =========================
       Core Handlers
    ========================= */

    public function on_comment($id, $comment) {
        // Skip WooCommerce order notes
        if (isset($comment->comment_type) && $comment->comment_type === 'order_note') {
            return;
        }

        // Skip if comment belongs to WooCommerce order post
        $post_type = get_post_type($comment->comment_post_ID);
        if ($post_type === 'shop_order') {
            return;
        }

        $title = sprintf(__('New comment by %s', 'notification-hub'), $comment->comment_author);
        $body  = wp_kses_post(wp_trim_words($comment->comment_content, 20));

        // Insert one clean record
        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification([
                'source'  => 'wp_core',
                'type'    => 'comment_new',
                'title'   => $title,
                'message' => $body,
                'context' => [
                    'comment_id' => (int)$id,
                    'post_id'    => (int)$comment->comment_post_ID
                ],
                'status'     => 'new',
                'created_at' => current_time('mysql'),
            ]);
        }

        // Unified send to all channels
        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $payload = [
                'title'   => $title,
                'body'    => $body,
                'source'  => 'wp_core',
                'no_log'  => true
            ];

            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
        }
    }

    public function on_post_status($new, $old, $post) {
        if ($new === $old) return;

        // Skip WooCommerce orders
        if (!is_object($post)) return;
        $type = isset($post->post_type) ? $post->post_type : get_post_type($post);
        if (in_array($type, ['shop_order', 'shop_order_placehold', 'shop_order_refund'], true)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("🧩 Skipped post_status_changed for WooCommerce order {$post->ID}");
            }
            return;
        }

        $e = [
            'source'  => 'wp_core',
            'type'    => 'post_status_changed',
            'title'   => sprintf(__('Post %d status: %s → %s', 'notification-hub'), $post->ID, $old, $new),
            'message' => esc_html(get_the_title($post->ID)),
            'context' => ['post_id' => $post->ID, 'old' => $old, 'new' => $new]
        ];

        $db = $this->r->get_svc('db');
        if ($db) $db->insert_notification($e);

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

    public function on_user_register($user_id) {
        $u = get_userdata($user_id);
        if (!$u) return;

        $e = [
            'source'  => 'wp_core',
            'type'    => 'user_registered',
            'title'   => sprintf(__('New user: %s', 'notification-hub'), $u->user_login),
            'message' => esc_html($u->user_email),
            'context' => ['user_id' => $user_id]
        ];

        $db = $this->r->get_svc('db');
        if ($db) $db->insert_notification($e);

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
