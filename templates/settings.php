<?php
/**
 * Settings Template
 *
 * Renders Notification Hub settings page (General / Pro tabs) and license box.
 * Tabs are driven by URL param `tab` and enhanced by JS (no inline scripts).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) exit;

/**
 * Read runtime flags
 */
$is_pro     = class_exists('NH_License') ? NH_License::is_pro() : false;
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

/**
 * Render admin notices from query params
 */
$channel = isset($_GET['nh_test']) ? sanitize_text_field($_GET['nh_test']) : '';
?>

<div class="wrap">
  <h1><?php esc_html_e('Notification Hub - Settings', 'notification-hub'); ?></h1>

  <?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] === '1'): ?>
      <div class="notice notice-success is-dismissible">
        <p>
          <?php
          echo sprintf(
            /* translators: %s: channel name (email/telegram/slack) */
            esc_html__('Test sent successfully to %s.', 'notification-hub'),
            '<strong>' . esc_html($channel ?: 'email') . '</strong>'
          );
          ?>
        </p>
      </div>
    <?php else: ?>
      <div class="notice notice-error is-dismissible">
        <p>
          <?php
          echo sprintf(
            /* translators: %s: channel name (email/telegram/slack) */
            esc_html__('Test failed to send to %s.', 'notification-hub'),
            '<strong>' . esc_html($channel ?: 'email') . '</strong>'
          );
          ?>
        </p>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (isset($_GET['settings-updated'])): ?>
    <div class="notice notice-success is-dismissible">
      <p><?php esc_html_e('Settings saved successfully.', 'notification-hub'); ?></p>
    </div>
  <?php endif; ?>

  <?php
  /**
   * Settings tabs (URL-based, JS enhances click UX)
   */
  ?>
  <h2 class="nav-tab-wrapper nh-settings-tabs" data-active-tab="<?php echo esc_attr($active_tab); ?>">
    <a
      href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=general')); ?>"
      class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"
      data-tab="general"
    >
      <?php esc_html_e('General', 'notification-hub'); ?>
    </a>

    <a
      href="<?php echo esc_url(admin_url('admin.php?page=nh_settings&tab=pro')); ?>"
      class="nav-tab <?php echo $active_tab === 'pro' ? 'nav-tab-active' : ''; ?>"
      data-tab="pro"
    >
      <?php esc_html_e('Pro Channels', 'notification-hub'); ?>
    </a>
  </h2>

  <?php
  /**
   * License box (only if NH_License exists)
   */
  if (class_exists('NH_License')):
    $current_key   = NH_License::get_key();
    $is_pro_active = NH_License::is_pro();

    // Mask key (keep first 4 + last 4 visible).
    $masked = '';
    if (!empty($current_key)) {
      $len = strlen($current_key);
      $masked = $len > 8
        ? substr($current_key, 0, 4) . str_repeat('•', $len - 8) . substr($current_key, -4)
        : $current_key;
    }
    ?>

    <div class="postbox nh-license-box">
      <h2 class="hndle">
        <span><?php esc_html_e('License & Pro Features', 'notification-hub'); ?></span>

        <?php if ($is_pro_active): ?>
          <span class="nh-license-status nh-license-status--active">
            <?php esc_html_e('PRO ACTIVE', 'notification-hub'); ?>
          </span>
        <?php else: ?>
          <span class="nh-license-status nh-license-status--locked">
            <?php esc_html_e('LOCKED', 'notification-hub'); ?>
          </span>
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
          <?php esc_html_e(
            'Enter your license key to unlock Pro features such as Telegram, Slack, multi-channel delivery, and advanced automation tools. Once activated, the Pro modules will automatically load and enhance your notification system.',
            'notification-hub'
          ); ?>
        </p>

        <p class="description">
          <?php esc_html_e(
            'If you purchased Notification Hub Pro, you should have received a license key in your account or email. Paste it below and click “Activate / Update License”. You can revoke it anytime to deactivate the Pro features.',
            'notification-hub'
          ); ?>
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
                  <input
                    type="text"
                    value="<?php echo esc_attr($masked); ?>"
                    readonly
                    class="regular-text nh-license-key--masked"
                  >

                  <a
                    href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=nh_license_revoke'), 'nh_license_revoke')); ?>"
                    class="button"
                  >
                    <?php esc_html_e('Revoke', 'notification-hub'); ?>
                  </a>

                  <p class="description nh-license-hint nh-license-hint--active">
                    <?php esc_html_e('Your license is active. Pro modules are loaded and ready to use.', 'notification-hub'); ?>
                  </p>
                <?php else: ?>
                  <input
                    type="text"
                    name="nh_license_key"
                    id="nh_license_key"
                    value=""
                    placeholder="<?php echo esc_attr($masked ?: esc_html__('Enter your license key', 'notification-hub')); ?>"
                    class="regular-text"
                  >

                  <?php submit_button(esc_html__('Activate / Update License', 'notification-hub'), 'primary', '', false); ?>

                  <p class="description nh-license-hint">
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

  <?php
  /**
   * Settings form
   */
  ?>
  <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
    <?php settings_fields('nh_settings'); do_settings_sections('nh_settings'); ?>

    <div
      id="nh-tab-general"
      class="nh-tab <?php echo $active_tab === 'general' ? 'is-active' : ''; ?>"
      data-tab="general"
    >
      <table class="form-table">
        <tr>
          <th>
            <label for="nh_retention_days"><?php esc_html_e('Retention (days)', 'notification-hub'); ?></label>
          </th>
          <td>
            <input
              name="nh_retention_days"
              id="nh_retention_days"
              type="number"
              min="1"
              value="<?php echo esc_attr(get_option('nh_retention_days', 90)); ?>"
            >
          </td>
        </tr>

        <tr>
          <th>
            <label for="nh_email_to"><?php esc_html_e('Email To', 'notification-hub'); ?></label>
          </th>
          <td>
            <input
              name="nh_email_to"
              id="nh_email_to"
              type="email"
              value="<?php echo esc_attr(get_option('nh_email_to', get_option('admin_email'))); ?>"
            >

            <p>
              <a
                href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=email&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=general')); ?>"
                data-tab="general"
                class="button nh-test-btn"
              >
                <?php esc_html_e('Send Test Email', 'notification-hub'); ?>
              </a>
            </p>
          </td>
        </tr>

        <tr>
          <th scope="row"><?php esc_html_e('Keep data on uninstall', 'notification-hub'); ?></th>
          <td>
            <label>
              <input type="hidden" name="nh_keep_data_on_uninstall" value="0">
              <input
                type="checkbox"
                name="nh_keep_data_on_uninstall"
                value="1"
                <?php checked(get_option('nh_keep_data_on_uninstall', true)); ?>
              />
              <?php esc_html_e('Do not delete plugin tables on uninstall', 'notification-hub'); ?>
            </label>
          </td>
        </tr>
      </table>
    </div>

    <div
      id="nh-tab-pro"
      class="nh-tab <?php echo $active_tab === 'pro' ? 'is-active' : ''; ?>"
      data-tab="pro"
    >
      <table class="form-table">
        <tr>
          <th><label for="nh_telegram_bot_token"><?php esc_html_e('Telegram Bot Token', 'notification-hub'); ?></label></th>
          <td>
            <input
              name="nh_telegram_bot_token"
              id="nh_telegram_bot_token"
              type="text"
              value="<?php echo esc_attr(get_option('nh_telegram_bot_token', '')); ?>"
              class="regular-text"
              <?php echo $is_pro ? '' : 'disabled'; ?>
            >

            <p class="description">
              <?php
              echo sprintf(
                /* translators: %s: example token */
                esc_html__('Enter your BotFather token. Example: %s', 'notification-hub'),
                '<code>123456:ABC-xyz</code>'
              );
              ?>
            </p>

            <?php if (!$is_pro): ?>
              <p class="description">
                <em><?php esc_html_e('This field is disabled because it’s only available in the Pro version.', 'notification-hub'); ?></em>
              </p>
            <?php else: ?>
              <p>
                <a
                  href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=telegram&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=pro')); ?>"
                  data-tab="pro"
                  class="button nh-test-btn"
                >
                  <?php esc_html_e('Send Test to Telegram', 'notification-hub'); ?>
                </a>
              </p>
            <?php endif; ?>
          </td>
        </tr>

        <tr>
          <th><label for="nh_telegram_chat_id"><?php esc_html_e('Telegram Chat ID', 'notification-hub'); ?></label></th>
          <td>
            <input
              name="nh_telegram_chat_id"
              id="nh_telegram_chat_id"
              type="text"
              value="<?php echo esc_attr(get_option('nh_telegram_chat_id', '')); ?>"
              class="regular-text"
              <?php echo $is_pro ? '' : 'disabled'; ?>
            >

            <p class="description">
              <?php esc_html_e('Send a message to your bot, then run /getUpdates to find your chat ID.', 'notification-hub'); ?>
            </p>

            <?php if (!$is_pro): ?>
              <p class="description">
                <em><?php esc_html_e('This field is disabled because it’s only available in the Pro version.', 'notification-hub'); ?></em>
              </p>
            <?php endif; ?>
          </td>
        </tr>

        <tr>
          <th><label for="nh_slack_webhook"><?php esc_html_e('Slack Webhook URL', 'notification-hub'); ?></label></th>
          <td>
            <input
              name="nh_slack_webhook"
              id="nh_slack_webhook"
              type="url"
              value="<?php echo esc_attr(get_option('nh_slack_webhook', '')); ?>"
              class="regular-text"
              <?php echo $is_pro ? '' : 'disabled'; ?>
            >

            <p class="description">
              <?php esc_html_e('Use an Incoming Webhook from Slack → App Integrations → Webhooks.', 'notification-hub'); ?>
            </p>

            <?php if (!$is_pro): ?>
              <p class="description">
                <em><?php esc_html_e('This field is disabled because it’s only available in the Pro version.', 'notification-hub'); ?></em>
              </p>
            <?php else: ?>
              <p>
                <a
                  href="<?php echo esc_url(admin_url('admin-post.php?action=nh_test_channel&channel=slack&_wpnonce=' . wp_create_nonce('nh_test_channel') . '&tab=pro')); ?>"
                  data-tab="pro"
                  class="button nh-test-btn"
                >
                  <?php esc_html_e('Send Test to Slack', 'notification-hub'); ?>
                </a>
              </p>
            <?php endif; ?>
          </td>
        </tr>
      </table>
    </div>

    <?php submit_button(); ?>
  </form>
</div>
