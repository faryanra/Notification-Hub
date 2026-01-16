<?php
/**
 * Hook CRUD Operations
 * 
 * @package Notification_Hub
 * @since 1.6.1
 */

if (!defined('ABSPATH')) exit;

class NH_Admin_Hooks {

    /**
     * Register hook action handlers
     */
    public static function init() {
        add_action('admin_post_nh_test_channel',  [__CLASS__, 'test_channel']);
        add_action('admin_post_nh_test_hook',     [__CLASS__, 'test_hook']);
        add_action('admin_post_nh_save_hook',     [__CLASS__, 'save']);
        add_action('admin_post_nh_update_hook',   [__CLASS__, 'update']);
        add_action('admin_post_nh_delete_hook',   [__CLASS__, 'delete']);
    }

    /**
     * Test a specific channel
     */
    public static function test_channel() {
        NH_Security::ensure_cap();
        NH_Security::verify_nonce('nh_test_channel');

        $channel  = sanitize_text_field($_GET['channel'] ?? '');
        $registry = NH_Core_Registry::get();
        $notifier = $registry->get_svc('notifier');

        if (!$notifier) wp_die('Notifier not available');

        $notifier->send($channel, [
            'subject' => '🔔 Notification Hub Test',
            'message' => 'This is a test notification.',
        ]);

        wp_safe_redirect(wp_get_referer());
        exit;
    }

    /**
     * Test a custom hook trigger
     */
    public static function test_hook() {
        NH_Security::ensure_cap();

        $id = NH_Security::request_int('id');
        if (!$id || !wp_verify_nonce($_GET['_wpnonce'], 'nh_test_' . $id)) {
            wp_die('Invalid request');
        }

        global $wpdb;
        $hook = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nh_hooks WHERE id=%d", $id)
        );

        if (!$hook) wp_die('Hook not found');

        $channels = json_decode($hook->channels, true) ?: ['email'];
        $notifier = NH_Core_Registry::get()->get_svc('notifier');

        foreach ($channels as $ch) {
            $notifier->send_now($ch, [
                'title'  => 'Test Hook: ' . $hook->title,
                'body'   => 'Triggered manually.',
                'source' => 'hook',
            ]);
        }

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&tested=1'));
        exit;
    }

    /**
     * Create new hook
     */
    public static function save() {
        NH_Security::ensure_cap();
        NH_Security::verify_nonce('nh_save_hook');

        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'nh_hooks',
            [
                'title'       => sanitize_text_field($_POST['title'] ?? ''),
                'action_name' => NH_Security::validate_action_name($_POST['action_name'] ?? ''),
                'channels'    => wp_json_encode($_POST['channels'] ?? []),
                'status'      => 1,
            ]
        );

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&saved=1'));
        exit;
    }

    /**
     * Update existing hook
     */
    public static function update() {
        NH_Security::ensure_cap();

        $id = NH_Security::request_int('id');
        NH_Security::verify_nonce('nh_update_hook', $id);

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'nh_hooks',
            [
                'title'       => sanitize_text_field($_POST['title'] ?? ''),
                'action_name' => NH_Security::validate_action_name($_POST['action_name'] ?? ''),
                'channels'    => wp_json_encode($_POST['channels'] ?? []),
            ],
            ['id' => $id]
        );

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&updated=1'));
        exit;
    }

    /**
     * Delete hook
     */
    public static function delete() {
        NH_Security::ensure_cap();

        $id = NH_Security::request_int('id');
        NH_Security::verify_nonce('nh_delete_hook', $id);

        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'nh_hooks', ['id' => $id]);

        wp_safe_redirect(admin_url('admin.php?page=nh-hooks&deleted=1'));
        exit;
    }
}
