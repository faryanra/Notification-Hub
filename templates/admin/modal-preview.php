<?php
/**
 * Notification Preview Modal
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="nh-preview-modal" class="nh-modal" style="display:none;">
	<div class="nh-modal-content">
		<span class="nh-modal-close">&times;</span>
		<h2><?php esc_html_e( 'Notification Preview', 'notification-hub' ); ?></h2>
		<div id="nh-preview-content"></div>
	</div>
</div>
