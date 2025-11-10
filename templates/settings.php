<?php
// Settings (Unified, Clean, Tab-Persistent + License Box)
if (!defined('ABSPATH')) exit;
$is_pro = class_exists('NH_License') ? NH_License::is_pro() : false;
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
?>
<div class="wrap">
  <h1><?php esc_html_e('Notification Hub - Settings','notification-hub'); ?></h1>

  <?php if (isset($_GET['success'])): ?>
    <?php $channel = sanitize_text_field($_GET['nh_test'] ?? ''); ?>
    <?php if ($_GET['success'] === '1'): ?>
      <div class="notice notice-success is-dismissible"><p>✅ Test sent successfully to <strong><?php echo esc_html($channel ?: 'email'); ?></strong>.</p></div>
    <?php else: ?>
      <div class="notice notice-error is-dismissible"><p>❌ Test failed to send to <strong><?php echo esc_html($channel ?: 'email'); ?></strong>.</p></div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (isset($_GET['settings-updated'])): ?>
    <div class="notice notice-success is-dismissible"><p>💾 Settings saved successfully.</p></div>
  <?php endif; ?>

  <h2 class="nav-tab-wrapper">
    <a href="?page=nh_settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
      <?php esc_html_e('General','notification-hub');?>
    </a>
    <a href="?page=nh_settings&tab=pro" class="nav-tab <?php echo $active_tab === 'pro' ? 'nav-tab-active' : ''; ?>">
      <?php esc_html_e('Pro Channels','notification-hub');?>
    </a>
  </h2>

  <?php
  if (class_exists('NH_License')):
    $current_key = NH_License::get_key();
    $is_pro_active = NH_License::is_pro();
    $masked = '';
    if (!empty($current_key)) {
      $len = strlen($current_key);
      $masked = $len > 8 ? substr($current_key, 0, 4) . str_repeat('•', $len - 8) . substr($current_key, -4) : $current_key;
    }
  ?>
  <div class="postbox" style="margin-top:20px;">
    <h2 class="hndle">
      <span><?php esc_html_e('License & Pro Features', 'notification-hub'); ?></span>
      <?php if ($is_pro_active): ?>
        <span style="color:#46b450; font-weight:bold; margin-left:8px;">PRO ACTIVE ✅</span>
      <?php else: ?>
        <span style="color:#a00; font-weight:bold; margin-left:8px;">LOCKED ❌</span>
      <?php endif; ?>
    </h2>
    <div class="inside">
      <?php if (isset($_GET['nh_license_saved'])): ?>
        <div class="notice notice-success is-dismissible">
          <p><?php esc_html_e('License key saved.', 'notification-hub'); ?></p>
        </div>
      <?php elseif (isset($_GET['nh_license_revoked'])): ?>
        <div class="notice notice-info is-dismissible">
          <p><?php esc_html_e('License key revoked.', 'notification-hub'); ?></p>
        </div>
      <?php endif; ?>

      <p class="description">
        <?php esc_html_e('Enter your license key to unlock Pro features such as Telegram, Slack, multi-channel delivery, and advanced automation tools. Once activated, the Pro modules will automatically load and enhance your notification system.', 'notification-hub'); ?>
      </p>

      <p class="description">
        <?php esc_html_e('If you purchased Notification Hub Pro, you should have received a license key in your account or email. Paste it below and click “Activate / Update License”. You can revoke it anytime to deactivate the Pro features.', 'notification-hub'); ?>
      </p>

      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('nh_save_license'); ?>
        <input type="hidden" name="action" value="nh_save_license" />
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row">
              <label for="nh_license_key"><?php esc_html_e('License Key', 'notification-hub'); ?></label>
            </th>
            <td>
              <?php if ($is_pro_active && $current_key): ?>
                <input type="text" value="<?php echo esc_attr($masked); ?>" readonly class="regular-text" style="opacity:0.6;">
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=nh_license_revoke'), 'nh_license_revoke'); ?>" class="button"><?php esc_html_e('Revoke', 'notification-hub'); ?></a>
                <p class="description" style="color:#46b450; margin-top:6px;">
                  <?php esc_html_e('Your license is active. Pro modules are loaded and ready to use.', 'notification-hub'); ?>
                </p>
              <?php else: ?>
                <input type="text" name="nh_license_key" value="" placeholder="<?php echo esc_attr($masked ?: 'Enter your license key'); ?>" class="regular-text">
                <?php submit_button(__('Activate / Update License', 'notification-hub'), 'primary', '', false); ?>
                <p class="description" style="margin-top:6px;">
                  <?php esc_html_e('Paste your valid license key to enable premium integrations and remove feature restrictions.', 'notification-hub'); ?>
                </p>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
    <input type="hidden" name="nh_active_tab" value="<?php echo esc_attr($active_tab); ?>">
    <?php settings_fields('nh_settings'); do_settings_sections('nh_settings'); ?>

    <div id="nh-tab-general" class="nh-tab" style="<?php echo $active_tab === 'general' ? '' : 'display:none;'; ?>">
      <table class="form-table">
        <tr>
          <th><label for="nh_retention_days"><?php esc_html_e('Retention (days)','notification-hub'); ?></label></th>
          <td><input name="nh_retention_days" id="nh_retention_days" type="number" min="1" value="<?php echo esc_attr(get_option('nh_retention_days',90)); ?>"></td>
        </tr>
        <tr>
          <th><label for="nh_email_to"><?php esc_html_e('Email To','notification-hub'); ?></label></th>
          <td>
            <input name="nh_email_to" id="nh_email_to" type="email" value="<?php echo esc_attr(get_option('nh_email_to',get_option('admin_email'))); ?>">
            <p><a href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=email&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=general')); ?>" data-tab="general" class="button nh-test-btn"><?php esc_html_e('Send Test Email','notification-hub'); ?></a></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Keep data on uninstall', 'notification-hub'); ?></th>
          <td>
            <label>
              <input type="hidden" name="nh_keep_data_on_uninstall" value="0">
              <input type="checkbox" name="nh_keep_data_on_uninstall" value="1" <?php checked( get_option('nh_keep_data_on_uninstall', true) ); ?> />
              <?php esc_html_e('Do not delete plugin tables on uninstall', 'notification-hub'); ?>
            </label>
          </td>
        </tr>
      </table>
    </div>

    <div id="nh-tab-pro" class="nh-tab" style="<?php echo $active_tab === 'pro' ? '' : 'display:none;'; ?>">
      <table class="form-table">
        <tr>
          <th><label><?php esc_html_e('Telegram Bot Token','notification-hub'); ?></label></th>
          <td>
            <input name="nh_telegram_bot_token" type="text" value="<?php echo esc_attr(get_option('nh_telegram_bot_token','')); ?>" class="regular-text" <?php echo $is_pro ? '' : 'disabled'; ?>>
            <p class="description">Enter your BotFather token. Example: <code>123456:ABC-xyz</code></p>
            <?php if (!$is_pro): ?>
              <p class="description"><em>🔒 This field is disabled because it’s only available in the Pro version.</em></p>
            <?php else: ?>
              <p><a href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=telegram&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=pro')); ?>" data-tab="pro" class="button nh-test-btn">Send Test to Telegram</a></p>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th><label><?php esc_html_e('Telegram Chat ID','notification-hub'); ?></label></th>
          <td>
            <input name="nh_telegram_chat_id" type="text" value="<?php echo esc_attr(get_option('nh_telegram_chat_id','')); ?>" class="regular-text" <?php echo $is_pro ? '' : 'disabled'; ?>>
            <p class="description">Send a message to your bot, then run <code>/getUpdates</code> to find your chat ID.</p>
            <?php if (!$is_pro): ?>
              <p class="description"><em>🔒 This field is disabled because it’s only available in the Pro version.</em></p>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th><label><?php esc_html_e('Slack Webhook URL','notification-hub'); ?></label></th>
          <td>
            <input name="nh_slack_webhook" type="url" value="<?php echo esc_attr(get_option('nh_slack_webhook','')); ?>" class="regular-text" <?php echo $is_pro ? '' : 'disabled'; ?>>
            <p class="description">Use an Incoming Webhook from Slack → App Integrations → Webhooks.</p>
            <?php if (!$is_pro): ?>
              <p class="description"><em>🔒 This field is disabled because it’s only available in the Pro version.</em></p>
            <?php else: ?>
              <p><a href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=slack&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=pro')); ?>" data-tab="pro" class="button nh-test-btn">Send Test to Slack</a></p>
            <?php endif; ?>
          </td>
        </tr>
      </table>
    </div>

    <?php submit_button(); ?>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const tabs = document.querySelectorAll('.nav-tab');
  const panes = document.querySelectorAll('.nh-tab');
  tabs.forEach(t => t.addEventListener('click', e => {
    e.preventDefault();
    tabs.forEach(x=>x.classList.remove('nav-tab-active'));
    t.classList.add('nav-tab-active');
    panes.forEach(p=>p.style.display='none');
    const href = t.getAttribute('href');
    const id = href.replace('?page=nh_settings&tab=','nh-tab-');
    document.getElementById(id).style.display = 'block';
    history.replaceState(null, '', href);
  }));
  document.querySelectorAll('.nh-test-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
      e.preventDefault();
      const tab = btn.dataset.tab || 'general';
      const href = new URL(btn.href);
      href.searchParams.set('tab', tab);
      window.location.href = href.toString();
    });
  });
});
</script>
