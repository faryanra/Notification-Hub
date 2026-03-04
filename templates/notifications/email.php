<?php
/**
 * Email notification template (HTML).
 *
 * Variables:
 * - $data (array)
 *
 * @package Notification_Hub
 * @since 1.6.3
 */

if (!defined('ABSPATH')) {
    exit;
}

$title   = isset($data['title']) ? wp_strip_all_tags((string) $data['title']) : '';
$summary = isset($data['summary']) ? (string) $data['summary'] : '';
$link    = isset($data['link']) ? (string) $data['link'] : '';
$src     = isset($data['source_human']) ? (string) $data['source_human'] : '';
$type    = isset($data['type_human']) ? (string) $data['type_human'] : '';
$actor   = isset($data['context']['actor']) ? (string) $data['context']['actor'] : '';
$site    = isset($data['site_name']) ? (string) $data['site_name'] : '';
$siteUrl = isset($data['site_url']) ? (string) $data['site_url'] : '';

$summary_safe = wpautop(wp_kses_post($summary));

$btn = '';
if ($link !== '') {
    $label = isset($data['cta_label']) ? wp_strip_all_tags((string) $data['cta_label']) : '';
    if ($label === '') {
        $label = esc_html__('View Details', 'notification-hub');
    }

    $btn = sprintf(
        '<a href="%s" style="display:inline-block;background:#2271b1;color:#fff;text-decoration:none;padding:12px 16px;border-radius:6px;font-weight:600;">%s</a>',
        esc_url($link),
        esc_html($label)
    );
}
?>
<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;background:#f6f7f7;padding:24px;">
  <div style="max-width:640px;margin:0 auto;">

    <div style="padding:8px 4px;color:#1d2327;font-size:18px;font-weight:700;">
      <?php echo esc_html__('Notification Hub', 'notification-hub'); ?>
    </div>

    <div style="background:#ffffff;border:1px solid #dcdcde;border-radius:10px;padding:20px;">
      <div style="font-size:18px;font-weight:700;color:#1d2327;line-height:1.3;">
        <?php echo esc_html($title !== '' ? $title : esc_html__('New Notification', 'notification-hub')); ?>
      </div>

      <?php if ($actor !== '') : ?>
        <div style="margin-top:10px;color:#50575e;font-size:13px;">
          <?php echo esc_html(sprintf(__('By: %s', 'notification-hub'), $actor)); ?>
        </div>
      <?php endif; ?>

      <div style="margin-top:14px;color:#1d2327;font-size:14px;line-height:1.6;">
        <?php echo $summary_safe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>

      <div style="margin-top:14px;color:#50575e;font-size:12px;">
        <?php echo esc_html(sprintf(__('Source: %1$s • Type: %2$s', 'notification-hub'), $src !== '' ? $src : '-', $type !== '' ? $type : '-')); ?>
      </div>

      <?php if ($btn !== '') : ?>
        <div style="margin-top:18px;">
          <?php echo $btn; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
      <?php endif; ?>
    </div>

    <div style="margin-top:14px;color:#787c82;font-size:12px;">
      <?php
      if ($site !== '' || $siteUrl !== '') {
          $siteText = $site !== '' ? $site : $siteUrl;
          echo esc_html(sprintf(__('Sent from %s', 'notification-hub'), $siteText));
      } else {
          echo esc_html__('Sent from your site', 'notification-hub');
      }
      ?>
    </div>

  </div>
</div>