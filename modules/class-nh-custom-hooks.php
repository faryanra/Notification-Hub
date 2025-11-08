<?php
// Custom Hooks Manager (Save, Test, Register Dynamic Actions)

if (!defined('ABSPATH')) exit;

class NH_Custom_Hooks {

  public function __construct() {
    add_action('admin_post_nh_save_hook', [$this,'save_hook']);
    add_action('admin_post_nh_test_hook', [$this,'test_hook']);

    // Dynamic registration of custom actions
    add_action('init', [$this,'register_dynamic_actions']);
  }

  /**
   * Save new hook to database
   */
  public function save_hook() {
    if ( ! current_user_can('manage_options') ) wp_die('Access denied');
    check_admin_referer('nh_save_hook');

    global $wpdb;
    $title    = sanitize_text_field($_POST['title'] ?? '');
    $action   = sanitize_key($_POST['action_name'] ?? '');
    $channels = isset($_POST['channels']) && is_array($_POST['channels']) ? array_map('sanitize_key', $_POST['channels']) : [];

    if (!$title || !$action) {
      wp_redirect( add_query_arg(['page'=>'nh-hooks','nh_err'=>1], admin_url('admin.php')) );
      exit;
    }

    // Prevent duplicates
    $exists = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}nh_hooks WHERE action_name=%s", $action) );
    if ($exists) {
      wp_redirect( add_query_arg(['page'=>'nh-hooks','nh_dup'=>1], admin_url('admin.php')) );
      exit;
    }

    // Insert hook
    $wpdb->insert($wpdb->prefix.'nh_hooks', [
      'title'       => $title,
      'action_name' => $action,
      'channels'    => wp_json_encode(array_values(array_unique($channels))),
      'status'      => 1
    ], ['%s','%s','%s','%d']);

    wp_redirect( add_query_arg(['page'=>'nh-hooks','saved'=>1], admin_url('admin.php')) );
    exit;
  }

  /**
   * Trigger a test event for a saved hook
   */
  public function test_hook() {
    if ( ! current_user_can('manage_options') ) wp_die('Access denied');
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    check_admin_referer('nh_test_'.$id);

    global $wpdb;
    $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nh_hooks WHERE id=%d", $id) );
    if (!$row) {
      wp_redirect( add_query_arg(['page'=>'nh-hooks','notfound'=>1], admin_url('admin.php')) );
      exit;
    }

    // Fire a test event
    do_action($row->action_name, [
      'test'    => true,
      'source'  => 'custom_hook_test',
      'message' => 'This is a test notification for hook: '.$row->action_name,
      'context' => ['hook_id'=>$row->id]
    ]);

    wp_redirect( add_query_arg(['page'=>'nh-hooks','tested'=>1], admin_url('admin.php')) );
    exit;
  }

  /**
   * Register all active custom hooks dynamically
   */
  public function register_dynamic_actions() {
    global $wpdb;
    $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nh_hooks WHERE status=1");
    if (!$rows) return;

    foreach ($rows as $r) {
      $action   = $r->action_name;
      $channels = json_decode($r->channels, true) ?: [];

      add_action($action, function($payload = []) use ($channels, $action) {
        if (!is_array($payload)) $payload = [];

        $message = $payload['message'] ?? ('Hook fired: '.$action);
        $source  = $payload['source'] ?? 'custom_hook';
        $context = $payload['context'] ?? [];

        // Add notification (if helper exists)
        if (function_exists('nh_add_notification')) {
          nh_add_notification([
            'source'  => $source,
            'message' => $message,
            'meta'    => wp_json_encode($context),
          ]);
        }

        // Send to all selected channels
        foreach ($channels as $ch) {
          do_action('nh_send_via_'.$ch, [
            'title'   => 'Notification Hub',
            'message' => $message,
            'source'  => $source,
            'context' => $context,
          ]);
        }
      }, 10, 1);
    }
  }
}
