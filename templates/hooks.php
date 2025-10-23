<?php if (!current_user_can('manage_options')) wp_die(__('Not allowed','notification-hub')); ?>
<?php
global $wpdb;
$table = $wpdb->prefix . 'nh_hooks';

/* Prefill edit */
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_row = null;
$edit_channels = [];
if ($edit_id > 0) {
  $edit_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $edit_id));
  if ($edit_row) {
    $tmp = json_decode($edit_row->channels, true);
    $edit_channels = is_array($tmp) ? $tmp : [];
  }
}

/* Notices */
foreach (['hook_saved'=>'✅ Hook created.',
          'hook_updated'=>'✅ Hook updated.',
          'hook_deleted'=>'🗑️ Hook deleted.',
          'hook_tested'=>'🚀 Test triggered.'] as $key=>$msg)
  if (!empty($_GET[$key]))
    echo '<div class="notice notice-success is-dismissible"><p>'.$msg.'</p></div>';
?>

<div class="wrap">
  <h1><?php _e('Custom Hooks','notification-hub'); ?></h1>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php
    if ($edit_row) {
      wp_nonce_field('nh_update_hook_' . $edit_row->id);
      echo '<input type="hidden" name="action" value="nh_update_hook">';
      echo '<input type="hidden" name="id" value="'.intval($edit_row->id).'">';
    } else {
      wp_nonce_field('nh_save_hook');
      echo '<input type="hidden" name="action" value="nh_save_hook">';
    }
    ?>
    <table class="form-table">
      <tr>
        <th><label for="nh_title"><?php _e('Title','notification-hub'); ?></label></th>
        <td><input name="title" id="nh_title" class="regular-text" required value="<?php echo esc_attr($edit_row->title ?? ''); ?>"></td>
      </tr>
      <tr>
        <th><label for="nh_action"><?php _e('Action name','notification-hub'); ?></label></th>
        <td><input name="action_name" id="nh_action" class="regular-text" required value="<?php echo esc_attr($edit_row->action_name ?? ''); ?>"></td>
      </tr>
      <tr>
        <th><?php _e('Channels','notification-hub'); ?></th>
        <td>
          <?php
          $channels = ['email'=>'Email','telegram'=>'Telegram','slack'=>'Slack'];
          foreach ($channels as $key=>$label) {
            $checked = in_array($key, $edit_channels, true);
            printf('<label style="margin-right:12px;"><input type="checkbox" name="channels[]" value="%s" %s> %s</label>',
              esc_attr($key),
              checked($checked, true, false),
              esc_html($label)
            );
          }
          ?>
        </td>
      </tr>
    </table>
    <?php
      submit_button($edit_row ? __('Update Hook','notification-hub') : __('Save Hook','notification-hub'));
      if ($edit_row)
        echo ' <a href="'.esc_url(admin_url('admin.php?page=nh-hooks')).'" class="button button-secondary">'.__('Cancel','notification-hub').'</a>';
    ?>
  </form>

  <hr>
  <h2><?php _e('Saved Hooks','notification-hub'); ?></h2>
  <?php
  $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC LIMIT 200");
  if ($rows) {
    echo '<table class="widefat striped"><thead><tr>
            <th>ID</th><th>'.__('Title','notification-hub').'</th>
            <th>'.__('Action','notification-hub').'</th>
            <th>'.__('Channels','notification-hub').'</th>
            <th>'.__('Status','notification-hub').'</th>
            <th style="width:260px;">'.__('Actions','notification-hub').'</th>
          </tr></thead><tbody>';
    foreach ($rows as $r) {
      $chs = json_decode($r->channels, true) ?: [];
      $test_nonce   = wp_create_nonce('nh_test_'.$r->id);
      $delete_nonce = wp_create_nonce('nh_delete_hook_'.$r->id);
      $test_url   = admin_url('admin-post.php?action=nh_test_hook&id='.$r->id.'&_wpnonce='.$test_nonce);
      $delete_url = admin_url('admin-post.php?action=nh_delete_hook&id='.$r->id.'&_wpnonce='.$delete_nonce);
      $edit_url   = admin_url('admin.php?page=nh-hooks&edit='.$r->id);

      echo '<tr>
        <td>'.intval($r->id).'</td>
        <td>'.esc_html($r->title).'</td>
        <td><code>'.esc_html($r->action_name).'</code></td>
        <td>'.esc_html(implode(', ', $chs)).'</td>
        <td>'.($r->status ? __('Active') : __('Inactive')).'</td>
        <td>
          <a class="button" href="'.esc_url($test_url).'">'.__('Trigger Test').'</a>
          <a class="button button-secondary" href="'.esc_url($edit_url).'">'.__('Edit').'</a>
          <a class="button button-link-delete" style="color:#b32d2e;" href="'.esc_url($delete_url).'" onclick="return confirm(\'Delete this hook?\')">'.__('Delete').'</a>
        </td>
      </tr>';
    }
    echo '</tbody></table>';
  } else {
    _e('No hooks yet.','notification-hub');
  }
  ?>
</div>
