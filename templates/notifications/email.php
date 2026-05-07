<?php
/**
 * Email notification template (HTML).
 *
 * Variables:
 * - $data (array)
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($data['title']) ? wp_strip_all_tags((string) $data['title']) : '';
$summary = isset($data['summary']) ? (string) $data['summary'] : '';
$link = isset($data['link']) ? (string) $data['link'] : '';
$source = isset($data['source_human']) ? wp_strip_all_tags((string) $data['source_human']) : '';
$type = isset($data['type_human']) ? wp_strip_all_tags((string) $data['type_human']) : '';
$site = isset($data['site_name']) ? wp_strip_all_tags((string) $data['site_name']) : '';
$site_url = isset($data['site_url']) ? (string) $data['site_url'] : '';
$actor = isset($data['context']['actor']) ? wp_strip_all_tags((string) $data['context']['actor']) : '';

if ($title === '') {
    $title = __('New Notification', 'notification-hub');
}

$summary_safe = wpautop(esc_html(wp_strip_all_tags($summary)));
$meta_source = $source !== '' ? $source : __('Unknown', 'notification-hub');
$meta_type = $type !== '' ? $type : __('General', 'notification-hub');
$cta_label = isset($data['cta_label']) ? wp_strip_all_tags((string) $data['cta_label']) : '';
if ($cta_label === '') {
    $cta_label = __('Open in WordPress', 'notification-hub');
}
?>
<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;background:#f6f7f7;padding:24px;">
  <div style="max-width:680px;margin:0 auto;">
    <div style="padding:8px 4px;color:#1d2327;font-size:18px;font-weight:700;">
      <?php echo esc_html__('Notification Hub', 'notification-hub'); ?>
    </div>

    <div style="background:#ffffff;border:1px solid #dcdcde;border-radius:10px;padding:22px;">
      <div style="font-size:20px;font-weight:700;color:#1d2327;line-height:1.3;">
        <?php echo esc_html($title); ?>
      </div>

      <?php if ($actor !== '') : ?>
        <div style="margin-top:8px;color:#50575e;font-size:13px;">
          <?php echo esc_html(sprintf(__('Triggered by: %s', 'notification-hub'), $actor)); ?>
        </div>
      <?php endif; ?>

      <div style="margin-top:14px;color:#1d2327;font-size:14px;line-height:1.7;">
        <?php echo $summary_safe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>

      <div style="margin-top:14px;color:#50575e;font-size:12px;">
        <?php
        echo esc_html(
            sprintf(
                __('Source: %1$s | Event: %2$s', 'notification-hub'),
                $meta_source,
                $meta_type
            )
        );
        ?>
      </div>

      <?php if ($link !== '') : ?>
        <div style="margin-top:18px;">
          <a href="<?php echo esc_url($link); ?>" style="display:inline-block;background:#2271b1;color:#ffffff;text-decoration:none;padding:12px 16px;border-radius:6px;font-weight:600;">
            <?php echo esc_html($cta_label); ?>
          </a>
        </div>
        <div style="margin-top:10px;color:#646970;font-size:12px;word-break:break-all;">
          <?php echo esc_html(sprintf(__('Direct link: %s', 'notification-hub'), $link)); ?>
        </div>
      <?php endif; ?>
    </div>

    <div style="margin-top:14px;color:#787c82;font-size:12px;">
      <?php
      if ($site !== '') {
          echo esc_html(sprintf(__('Sent from %s', 'notification-hub'), $site));
      } elseif ($site_url !== '') {
          echo esc_html(sprintf(__('Sent from %s', 'notification-hub'), $site_url));
      } else {
          echo esc_html__('Sent from your website', 'notification-hub');
      }
      ?>
    </div>
  </div>
</div>
