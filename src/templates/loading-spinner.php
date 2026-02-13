<?php
/**
 * Loading Spinner Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = $args['message'] ?? __( 'Loading...', 'notification-hub' );
?>

<div class="nh-loading-spinner">
	<span class="spinner is-active"></span>
	<p><?php echo esc_html( $message ); ?></p>
</div>
