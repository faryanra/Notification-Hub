<?php
// NH v1.2.0 — Dashboard template (simple list)
if (!defined('ABSPATH')) exit;
$registry = NH_Core_Registry::get();
$db = $registry->get_svc('db');
$rows = $db->get_list([],1,20);
?>
<div class="wrap">
  <h1><?php esc_html_e('Notification Hub','notification-hub'); ?></h1>
  <table class="widefat striped">
    <thead><tr>
      <th><?php esc_html_e('ID','notification-hub');?></th>
      <th><?php esc_html_e('Title','notification-hub');?></th>
      <th><?php esc_html_e('Source','notification-hub');?></th>
      <th><?php esc_html_e('Type','notification-hub');?></th>
      <th><?php esc_html_e('Created','notification-hub');?></th>
    </tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo esc_html($r['title']); ?></td>
          <td><?php echo esc_html($r['source']); ?></td>
          <td><?php echo esc_html($r['type']); ?></td>
          <td><?php echo esc_html($r['created_at']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
