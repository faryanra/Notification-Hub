<?php
/**
 * Upgrade Panel Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_premium = defined( 'NH_PRO_ACTIVE' ) && NH_PRO_ACTIVE;

if ( $is_premium ) {
	return;
}
?>

<div class="nh-upgrade-panel">
	<h2><?php esc_html_e( '🚀 Upgrade to Premium', 'notification-hub' ); ?></h2>
	
	<p><?php esc_html_e( 'Unlock powerful features:', 'notification-hub' ); ?></p>
	
	<ul>
		<li>✅ <?php esc_html_e( 'Telegram Notifications', 'notification-hub' ); ?></li>
		<li>✅ <?php esc_html_e( 'Slack Notifications', 'notification-hub' ); ?></li>
		<li>✅ <?php esc_html_e( 'Priority Support', 'notification-hub' ); ?></li>
		<li>✅ <?php esc_html_e( 'Advanced Custom Hooks', 'notification-hub' ); ?></li>
	</ul>
	
	<p>
		<a href="https://hellocode.ir/notification-hub-premium" class="button button-primary" target="_blank">
			<?php esc_html_e( 'Get Premium Now', 'notification-hub' ); ?>
		</a>
	</p>
</div>
