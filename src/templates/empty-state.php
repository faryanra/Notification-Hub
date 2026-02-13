<?php
/**
 * Empty State Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = $args['message'] ?? __( 'No notifications found.', 'notification-hub' );
$icon    = $args['icon'] ?? 'bell';
?>

<div class="nh-empty-state">
	<div class="nh-empty-state__icon">
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	</div>
	<p class="nh-empty-state__message"><?php echo esc_html( $message ); ?></p>
</div>
