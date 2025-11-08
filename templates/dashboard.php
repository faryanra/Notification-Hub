<?php
// Basic Dashboard Table Template
if (!defined('ABSPATH')) exit;

$registry = NH_Core_Registry::get();
$db       = $registry->get_svc('db');
$rows     = $db->get_list([], 1, 20);
?>

<div class="wrap">
  <h1><?php esc_html_e('Notification Hub', 'notification-hub'); ?></h1>

  <div id="nh-refresh-indicator" hidden>⏳ Updating...</div>

  <table class="widefat striped">
    <thead>
      <tr>
        <th><?php esc_html_e('ID', 'notification-hub'); ?></th>
        <th><?php esc_html_e('Title', 'notification-hub'); ?></th>
        <th><?php esc_html_e('Source', 'notification-hub'); ?></th>
        <th><?php esc_html_e('Type', 'notification-hub'); ?></th>
        <th><?php esc_html_e('Created', 'notification-hub'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?php echo (int) $row['id']; ?></td>
          <td><?php echo esc_html($row['title']); ?></td>
          <td><?php echo esc_html($row['source']); ?></td>
          <td><?php echo esc_html($row['type']); ?></td>
          <td><?php echo esc_html(mysql2date('Y-m-d H:i', $row['created_at'])); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
