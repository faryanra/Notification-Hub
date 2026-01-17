<?php
/**
 * NH_Custom_Hooks
 *
 * Custom Hooks manager (save, test, and dynamic action registration).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Custom_Hooks {

    /**
     * Registry instance.
     *
     * @since 1.6.2
     * @var mixed
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry = null) {
        $this->r = $registry;

        add_action('admin_post_nh_save_hook', [$this, 'save_hook']);
        add_action('admin_post_nh_test_hook', [$this, 'test_hook']);
        add_action('admin_post_nh_update_hook', [$this, 'update_hook']);
        add_action('admin_post_nh_delete_hook', [$this, 'delete_hook']);

        // Dynamic registration of custom actions.
        add_action('init', [$this, 'register_dynamic_actions']);
    }

    /**
     * Optional static init helper (matches loader pattern).
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     * @return void
     */
    public static function init($registry = null): void {
        new self($registry);
    }

    /**
     * Save a new hook into database.
     *
     * @since 1.6.2
     * @return void
     */
    public function save_hook(): void {
        NH_Security::ensure_cap();
        NH_Security::verify_nonce('nh_save_hook');

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        $title      = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $raw_action = isset($_POST['action_name']) ? (string) wp_unslash($_POST['action_name']) : '';
        $action     = NH_Security::validate_action_name($raw_action);

        $channels = isset($_POST['channels']) ? (array) wp_unslash($_POST['channels']) : [];
        $channels = NH_Security::sanitize_channels($channels);

        if ($title === '' || $action === '') {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'nh_err' => 1], admin_url('admin.php')));
            exit;
        }

        // Prevent duplicates.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE action_name=%s", $action));

        if ($exists > 0) {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'nh_dup' => 1], admin_url('admin.php')));
            exit;
        }

        $wpdb->insert(
            $table,
            [
                'title'       => $title,
                'action_name' => $action,
                'channels'    => $channels ? wp_json_encode(array_values(array_unique($channels))) : null,
                'status'      => 1,
            ],
            ['%s', '%s', '%s', '%d']
        );

        // Match template notices keys.
        wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'hook_saved' => 1], admin_url('admin.php')));
        exit;
    }

    /**
     * Trigger a test event for a saved hook.
     *
     * @since 1.6.2
     * @return void
     */
    public function test_hook(): void {
        NH_Security::ensure_cap();

        $id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
        NH_Security::verify_nonce('nh_test_hook', $id);

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id));
        if (!$row) {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'notfound' => 1], admin_url('admin.php')));
            exit;
        }

        do_action((string) $row->action_name, [
            'test'    => true,
            'source'  => 'custom_hook_test',
            /* translators: %s: hook action name */
            'message' => sprintf(__('This is a test notification for hook: %s', 'notification-hub'), (string) $row->action_name),
            'context' => ['hook_id' => (int) $row->id],
        ]);

        wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'hook_tested' => 1], admin_url('admin.php')));
        exit;
    }

    /**
     * Update an existing hook.
     *
     * @since 1.6.2
     * @return void
     */
    public function update_hook(): void {
        NH_Security::ensure_cap();

        $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
        NH_Security::verify_nonce('nh_update_hook', $id);

        if ($id <= 0) {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'nh_err' => 1], admin_url('admin.php')));
            exit;
        }

        $title      = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $raw_action = isset($_POST['action_name']) ? (string) wp_unslash($_POST['action_name']) : '';
        $action     = NH_Security::validate_action_name($raw_action);

        $channels = isset($_POST['channels']) ? (array) wp_unslash($_POST['channels']) : [];
        $channels = NH_Security::sanitize_channels($channels);

        if ($title === '' || $action === '') {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'nh_err' => 1, 'edit' => $id], admin_url('admin.php')));
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        // Prevent duplicates (same action_name on other rows).
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $dup = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE action_name=%s AND id<>%d",
                $action,
                $id
            )
        );

        if ($dup > 0) {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'nh_dup' => 1, 'edit' => $id], admin_url('admin.php')));
            exit;
        }

        $wpdb->update(
            $table,
            [
                'title'       => $title,
                'action_name' => $action,
                'channels'    => $channels ? wp_json_encode(array_values(array_unique($channels))) : null,
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'hook_updated' => 1], admin_url('admin.php')));
        exit;
    }

    /**
     * Delete a hook.
     *
     * @since 1.6.2
     * @return void
     */
    public function delete_hook(): void {
        NH_Security::ensure_cap();

        $id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
        NH_Security::verify_nonce('nh_delete_hook', $id);

        if ($id <= 0) {
            wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'nh_err' => 1], admin_url('admin.php')));
            exit;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'nh_hooks';

        $wpdb->delete($table, ['id' => $id], ['%d']);

        wp_safe_redirect(add_query_arg(['page' => 'nh-hooks', 'hook_deleted' => 1], admin_url('admin.php')));
        exit;
    }

    /**
     * Register all active custom hooks dynamically.
     *
     * When a custom hook fires, this will:
     * - Add a notification into nh_notifications table via NH_Database
     * - Enqueue delivery via NH_Queue for selected channels
     *
     * @since 1.6.2
     * @return void
     */
    public function register_dynamic_actions(): void {
        global $wpdb;

        $table = $wpdb->prefix . 'nh_hooks';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results("SELECT * FROM {$table} WHERE status=1");

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $r) {
            $action   = (string) $r->action_name;
            $channels = json_decode((string) $r->channels, true);
            $channels = is_array($channels) ? $channels : [];

            add_action(
                $action,
                function ($payload = []) use ($channels, $action) {
                    if (!is_array($payload)) {
                        $payload = [];
                    }

                    $message = isset($payload['message']) ? (string) $payload['message'] : '';
                    if ($message === '') {
                        /* translators: %s: hook action name */
                        $message = sprintf(__('Hook fired: %s', 'notification-hub'), $action);
                    }

                    $source  = isset($payload['source']) ? sanitize_text_field((string) $payload['source']) : 'custom_hook';
                    $context = isset($payload['context']) && is_array($payload['context']) ? $payload['context'] : [];

                    // 1) Store notification.
                    $db = $this->r && method_exists($this->r, 'get_svc') ? $this->r->get_svc('db') : null;
                    if ($db && method_exists($db, 'insert_notification')) {
                        $db->insert_notification([
                            'source'  => $source,
                            'type'    => 'custom_hook',
                            'title'   => $action,
                            'message' => $message,
                            'status'  => 0,
                            'context' => $context,
                            'tags'    => ['custom_hook', $action],
                        ]);
                    }

                    // 2) Send via selected channels.
                    if (!empty($channels) && class_exists('NH_Queue')) {
                        foreach ($channels as $ch) {
                            $ch = sanitize_key((string) $ch);
                            if ($ch === '') {
                                continue;
                            }

                            NH_Queue::enqueue_send($ch, [
                                'title'   => __('Notification Hub', 'notification-hub'),
                                'body'    => $message,
                                'source'  => $source,
                                'context' => $context,
                            ]);
                        }
                    }
                },
                10,
                1
            );
        }
    }
}