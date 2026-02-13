<?php
/**
 * License Debug Panel Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	return;
}

$license_key    = get_option( 'nh_license_key', '' );
$license_status = get_option( 'nh_license_status', 'inactive' );
$license_server = get_option( 'nh_license_server', '' );
?>

<div class="nh-debug-panel">
	<h3><?php esc_html_e( 'Debug Info', 'notification-hub' ); ?></h3>
	
	<table class="widefat">
		<tr>
			<td><strong><?php esc_html_e( 'License Key', 'notification-hub' ); ?>:</strong></td>
			<td><code><?php echo esc_html( $license_key ?: 'N/A' ); ?></code></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Status', 'notification-hub' ); ?>:</strong></td>
			<td><code><?php echo esc_html( $license_status ); ?></code></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Server', 'notification-hub' ); ?>:</strong></td>
			<td><code><?php echo esc_html( $license_server ?: 'N/A' ); ?></code></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Domain', 'notification-hub' ); ?>:</strong></td>
			<td><code><?php echo esc_html( home_url() ); ?></code></td>
		</tr>
	</table>
</div>
