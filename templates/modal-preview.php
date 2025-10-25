<?php
// Modal preview template (Dashboard View)
if (!defined('ABSPATH')) exit;
?>

<div id="nh-modal" class="nh-modal" style="display:none;">
  <div class="nh-modal__backdrop"></div>
  <div class="nh-modal__content">
    <button class="nh-modal__close" type="button">×</button>
    <h2 id="nh-modal-title"></h2>
    <div id="nh-modal-message" style="margin:10px 0;"></div>
    <div id="nh-modal-meta" style="font-size:12px;color:#777;"></div>
  </div>
</div>
