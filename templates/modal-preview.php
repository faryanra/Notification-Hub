<?php
// NH v1.3.8 — Modal Preview (Accessible)
if (!defined('ABSPATH')) exit;
?>
<div id="nh-modal" class="nh-modal" role="dialog" aria-modal="true" aria-labelledby="nh-modal-title" aria-describedby="nh-modal-message" style="display:none;">
  <div class="nh-modal__backdrop" tabindex="-1"></div>
  <div class="nh-modal__content" role="document">
    <button type="button" class="nh-modal__close" aria-label="<?php esc_attr_e('Close dialog','notification-hub'); ?>">×</button>
    <h2 id="nh-modal-title" class="nh-modal__title"><?php esc_html_e('Notification Details','notification-hub'); ?></h2>
    <div id="nh-modal-message" class="nh-modal__message"></div>
    <div id="nh-modal-meta" class="nh-modal__meta"></div>
  </div>
</div>
