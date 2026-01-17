<?php
/**
 * NH_Int_WP_Core
 *
 * WordPress core integration and custom hooks loader.
 *
 * - Listens to core WP events (comment, post status change, user register).
 * - Loads custom hooks from DB and triggers notifications when they fire.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Int_WP_Core {

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
        $this->hooks();
    }

    /**
     * Register WP hooks.
     *
     * @since 1.6.2
     * @return void
     */
    public function hooks() {
        // Core events.
        add_action('wp_insert_comment', [$this, 'on_comment'], 10, 2);
        add_action('transition_post_status', [$this, 'on_post_status'], 10, 3);
        add_action('user_register', [$this, 'on_user_register'], 10, 1);

        // Custom hooks from DB (table: wp_nh_hooks).
        $this->register_custom_hooks();
    }

    /**
     * Register custom hooks that are stored in DB (nh_hooks table).
     *
     * @since 1.6.2
     * @return void
     */
    protected function register_custom_hooks() {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_hooks';

        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            return;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results("SELECT id, action_name, title, channels FROM {$table}");
        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $hook_name = trim((string) ($row->action_name ?? ''));
            if ($hook_name === '') {
                continue;
            }

            $channels = $this->decode_channels($row->channels ?? '');
            $primary  = $channels[0] ?? 'email';

            add_action(
                $hook_name,
                function (...$args) use ($row, $hook_name, $primary) {
                    $payload = $this->normalize_payload_from_args($row, $hook_name, $args);

                    $event = [
                        'source'  => 'hook',
                        'type'    => $hook_name,
                        'title'   => $payload['title'] ?? ($row->title ?: sprintf(esc_html__('Hook: %s', 'notification-hub'), $hook_name)),
                        'message' => $payload['body'] ?? '',
                        'context' => ['hook' => $hook_name],
                    ];

                    $db = $this->r->get_svc('db');
                    if ($db && method_exists($db, 'insert_notification')) {
                        $db->insert_notification($event);
                    }

                    $notifier = $this->r->get_svc('notifier');
                    if ($notifier && method_exists($notifier, 'queue_send')) {
                        $notifier->queue_send(
                            $primary,
                            [
                                'title'  => $event['title'],
                                'body'   => $event['message'],
                                'source' => $event['source'],
                                'no_log' => true,
                            ]
                        );
                    }
                },
                10,
                10
            );
        }
    }

    /**
     * Decode channels JSON to a clean list of supported channels.
     *
     * @since 1.6.2
     * @param mixed $json Channels json.
     * @return array<int, string>
     */
    protected function decode_channels($json): array {
        if (!is_string($json) || $json === '') {
            return ['email'];
        }

        $arr = json_decode($json, true);
        if (!is_array($arr) || empty($arr)) {
            return ['email'];
        }

        return array_values(
            array_filter(
                array_map(
                    static function ($c) {
                        $c = strtolower(trim((string) $c));
                        return in_array($c, ['email', 'telegram', 'slack'], true) ? $c : null;
                    },
                    $arr
                )
            )
        );
    }

    /**
     * Normalize payload from action args.
     *
     * If the first argument is an array, it can define:
     * - title
     * - body
     * - source
     *
     * @since 1.6.2
     * @param object $row Hook row.
     * @param string $hook_name Hook name.
     * @param array  $args Action args.
     * @return array<string, mixed>
     */
    protected function normalize_payload_from_args($row, $hook_name, array $args): array {
        if (!empty($args) && is_array($args[0])) {
            $p = $args[0];

            $fallback_title = $row->title ?: sprintf(esc_html__('Hook: %s', 'notification-hub'), $hook_name);

            return [
                'title'  => isset($p['title']) ? sanitize_text_field((string) $p['title']) : $fallback_title,
                'body'   => isset($p['body']) ? sanitize_textarea_field((string) $p['body']) : '',
                'source' => isset($p['source']) ? sanitize_text_field((string) $p['source']) : 'hook',
            ];
        }

        $summary = '';
        if (!empty($args)) {
            $summary = wp_json_encode(array_slice($args, 0, 3));
        }

        return [
            'title'  => $row->title ?: sprintf(esc_html__('Hook: %s', 'notification-hub'), $hook_name),
            'body'   => $summary,
            'source' => 'hook',
        ];
    }

    /**
     * Handle new comment notifications.
     *
     * @since 1.6.2
     * @param int    $id Comment ID.
     * @param object $comment WP_Comment.
     * @return void
     */
    public function on_comment($id, $comment) {
        // Skip WooCommerce order notes.
        if (isset($comment->comment_type) && $comment->comment_type === 'order_note') {
            return;
        }

        // Skip if comment belongs to WooCommerce order post.
        $post_type = get_post_type($comment->comment_post_ID);
        if ($post_type === 'shop_order') {
            return;
        }

        /* translators: %s: Comment author name. */
        $title = sprintf(esc_html__('New comment by %s', 'notification-hub'), (string) $comment->comment_author);
        $body  = wp_kses_post(wp_trim_words((string) $comment->comment_content, 20));

        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification(
                [
                    'source'  => 'wp_core',
                    'type'    => 'comment_new',
                    'title'   => $title,
                    'message' => $body,
                    'context' => [
                        'comment_id' => (int) $id,
                        'post_id'    => (int) $comment->comment_post_ID,
                    ],
                ]
            );
        }

        // Unified send to all channels.
        $notifier = $this->r->get_svc('notifier');
        if ($notifier && method_exists($notifier, 'queue_send')) {
            $payload = [
                'title'  => $title,
                'body'   => $body,
                'source' => 'wp_core',
                'no_log' => true,
            ];

            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
        }
    }

    /**
     * Handle post status changes.
     *
     * @since 1.6.2
     * @param string $new New status.
     * @param string $old Old status.
     * @param object $post WP_Post.
     * @return void
     */
    public function on_post_status($new, $old, $post) {
        if ($new === $old) {
            return;
        }

        // Skip WooCommerce orders.
        if (!is_object($post)) {
            return;
        }

        $type = isset($post->post_type) ? $post->post_type : get_post_type($post);
        if (in_array($type, ['shop_order', 'shop_order_placehold', 'shop_order_refund'], true)) {
            return;
        }

        /* translators: 1: Post ID, 2: Old status, 3: New status. */
        $title = sprintf(esc_html__('Post %1$d status: %2$s → %3$s', 'notification-hub'), (int) $post->ID, (string) $old, (string) $new);
        $message = esc_html(get_the_title($post->ID));

        $e = [
            'source'  => 'wp_core',
            'type'    => 'post_status_changed',
            'title'   => $title,
            'message' => $message,
            'context' => ['post_id' => (int) $post->ID, 'old' => (string) $old, 'new' => (string) $new],
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
     * Handle user registration.
     *
     * @since 1.6.2
     * @param int $user_id User ID.
     * @return void
     */
    public function on_user_register($user_id) {
        $u = get_userdata($user_id);
        if (!$u) {
            return;
        }

        /* translators: %s: Username. */
        $title = sprintf(esc_html__('New user: %s', 'notification-hub'), (string) $u->user_login);
        $message = esc_html((string) $u->user_email);

        $e = [
            'source'  => 'wp_core',
            'type'    => 'user_registered',
            'title'   => $title,
            'message' => $message,
            'context' => ['user_id' => (int) $user_id],
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